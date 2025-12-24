<?php

/**
 * ARSITEKTUR VIDEO PLATFORM - BAGIAN 2: CONTROLLERS & MIDDLEWARE
 */

// ============================================================================
// BAGIAN 4: MIDDLEWARE
// ============================================================================

// app/Http/Middleware/CheckUploader.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUploader
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check() || ! auth()->user()->isUploader()) {
            abort(403, 'Access denied. Uploader only.');
        }

        return $next($request);
    }
}

// app/Http/Middleware/CheckAdmin.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            abort(403, 'Access denied. Admin only.');
        }

        return $next($request);
    }
}

// app/Http/Middleware/RateLimitStreaming.php

namespace App\Http\Middleware;

use App\Models\SecurityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RateLimitStreaming
{
    public function handle(Request $request, Closure $next)
    {
        $ip = $request->ip();
        $key = 'stream_limit:'.$ip;
        $limit = 100; // max 100 request per menit

        $current = Cache::get($key, 0);

        if ($current >= $limit) {
            SecurityLog::create([
                'event_type' => 'rate_limit_exceeded',
                'ip_address' => $ip,
                'user_id' => auth()->id(),
                'details' => 'Streaming rate limit exceeded',
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            abort(429, 'Too many requests. Please wait.');
        }

        Cache::put($key, $current + 1, 60);

        return $next($request);
    }
}

// app/Http/Middleware/ValidateVideoToken.php

namespace App\Http\Middleware;

use App\Models\SecurityLog;
use App\Models\VideoToken;
use Closure;
use Illuminate\Http\Request;

class ValidateVideoToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->route('token');
        $videoToken = VideoToken::with('video')->find($token);

        if (! $videoToken) {
            $this->logInvalidToken($request, 'Token not found');
            abort(403, 'Invalid token');
        }

        if (! $videoToken->isValid($request->ip(), $request->session()->getId())) {
            $this->logInvalidToken($request, 'Token expired or IP/session mismatch');
            abort(403, 'Token expired or invalid');
        }

        $request->attributes->set('videoToken', $videoToken);
        $request->attributes->set('video', $videoToken->video);

        return $next($request);
    }

    private function logInvalidToken(Request $request, string $reason): void
    {
        SecurityLog::create([
            'event_type' => 'invalid_video_token',
            'ip_address' => $request->ip(),
            'user_id' => auth()->id(),
            'details' => $reason,
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}

// ============================================================================
// BAGIAN 5: FORM REQUESTS
// ============================================================================

// app/Http/Requests/StoreVideoRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVideoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isUploader();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'video' => 'required|file|mimes:mp4,mov,avi,webm,mkv|max:512000', // 500MB
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', // 2MB
            'is_public' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'video.max' => 'Video tidak boleh lebih dari 500MB',
            'video.mimes' => 'Format video harus: mp4, mov, avi, webm, mkv',
            'thumbnail.max' => 'Thumbnail tidak boleh lebih dari 2MB',
        ];
    }
}

// app/Http/Requests/StorePayoutRequest.php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class StorePayoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isUploader();
    }

    public function rules(): array
    {
        $minPayout = Setting::get('min_payout', 100000);
        $maxPayout = auth()->user()->balance;

        return [
            'amount' => "required|numeric|min:{$minPayout}|max:{$maxPayout}",
            'payment_method' => 'required|in:bank_transfer,dana,gopay,ovo,shopeepay',
            'payment_account' => 'required|string|max:50',
            'payment_name' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Minimum withdrawal adalah Rp '.number_format(Setting::get('min_payout', 100000)),
            'amount.max' => 'Saldo tidak mencukupi',
        ];
    }
}

// ============================================================================
// BAGIAN 6: SERVICES
// ============================================================================

// app/Services/StreamingService.php

namespace App\Services;

use App\Models\Video;
use App\Models\VideoToken;
use App\Models\VideoView;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StreamingService
{
    /**
     * Generate token untuk streaming video
     */
    public function generateToken(Video $video, Request $request): string
    {
        // Hapus token lama dari user/IP yang sama
        VideoToken::where('video_id', $video->id)
            ->where('ip_address', $request->ip())
            ->where('expires_at', '<', now())
            ->delete();

        $token = Str::random(64);

        VideoToken::create([
            'token' => $token,
            'video_id' => $video->id,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
            'session_id' => $request->session()->getId(),
            'ad_watched' => false,
            'expires_at' => now()->addMinutes(30),
            'created_at' => now(),
        ]);

        return $token;
    }

    /**
     * Stream video dengan Range Request support (untuk seeking)
     */
    public function streamVideo(Video $video, Request $request): StreamedResponse
    {
        $path = $video->getStoragePath();
        $fileSize = filesize($path);
        $mimeType = $video->mime_type ?: 'video/mp4';

        $start = 0;
        $end = $fileSize - 1;
        $length = $fileSize;
        $statusCode = 200;

        // Handle Range Request untuk seeking
        if ($request->hasHeader('Range')) {
            $range = $request->header('Range');

            if (preg_match('/bytes=(\d+)-(\d*)/', $range, $matches)) {
                $start = intval($matches[1]);
                $end = isset($matches[2]) && $matches[2] !== ''
                    ? intval($matches[2])
                    : $fileSize - 1;

                $length = $end - $start + 1;
                $statusCode = 206; // Partial Content
            }
        }

        $headers = [
            'Content-Type' => $mimeType,
            'Content-Length' => $length,
            'Accept-Ranges' => 'bytes',
            'Content-Range' => "bytes {$start}-{$end}/{$fileSize}",
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            // Security headers
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
        ];

        return response()->stream(function () use ($path, $start, $length) {
            $stream = fopen($path, 'rb');
            fseek($stream, $start);

            $remaining = $length;
            $bufferSize = 8192; // 8KB chunks

            while (! feof($stream) && $remaining > 0) {
                $readSize = min($bufferSize, $remaining);
                echo fread($stream, $readSize);
                $remaining -= $readSize;
                flush();

                // Cegah memory leak
                if (connection_aborted()) {
                    break;
                }
            }

            fclose($stream);
        }, $statusCode, $headers);
    }

    /**
     * Catat view video
     */
    public function recordView(Video $video, Request $request): bool
    {
        $sessionId = $request->session()->getId();
        $ip = $request->ip();
        $isMember = auth()->check() && auth()->user()->hasActiveMembership();

        // Cek apakah sudah ada view dari IP+session dalam 24 jam
        $exists = VideoView::where('video_id', $video->id)
            ->where('session_id', $sessionId)
            ->where('ip_address', $ip)
            ->where('created_at', '>', now()->subHours(24))
            ->exists();

        if ($exists) {
            return false;
        }

        VideoView::create([
            'video_id' => $video->id,
            'user_id' => auth()->id(),
            'ip_address' => $ip,
            'session_id' => $sessionId,
            'user_agent' => $request->userAgent(),
            'is_member_view' => $isMember,
            'is_counted' => false,
            'created_at' => now(),
        ]);

        $video->increment('total_views');

        return true;
    }
}

// app/Services/EarningService.php

namespace App\Services;

use App\Models\AdConfig;
use App\Models\Earning;
use App\Models\Video;
use App\Models\VideoView;
use Illuminate\Support\Facades\DB;

class EarningService
{
    /**
     * Hitung penghasilan harian dari views
     */
    public function calculateDailyEarnings(?string $date = null): array
    {
        $date = $date ?? now()->subDay()->toDateString();

        // Ambil CPM rate rata-rata dari iklan aktif
        $cpmRate = AdConfig::where('is_active', true)->avg('cpm_rate') ?? 2.00;

        $results = [];

        // Ambil views yang belum dihitung (bukan member views)
        $viewsByVideo = VideoView::where('is_counted', false)
            ->where('is_member_view', false)
            ->whereDate('created_at', '<=', $date)
            ->groupBy('video_id')
            ->selectRaw('video_id, count(*) as views_count')
            ->get();

        foreach ($viewsByVideo as $item) {
            $video = Video::with('user')->find($item->video_id);
            if (! $video || ! $video->user) {
                continue;
            }

            // Hitung penghasilan: (views / 1000) * CPM
            $amount = ($item->views_count / 1000) * $cpmRate;

            DB::transaction(function () use ($video, $item, $amount, $cpmRate, $date) {
                // Buat record earning
                Earning::updateOrCreate(
                    ['video_id' => $video->id, 'calculation_date' => $date],
                    [
                        'user_id' => $video->user_id,
                        'views_count' => $item->views_count,
                        'cpm_rate' => $cpmRate,
                        'amount' => $amount,
                    ]
                );

                // Update balance user
                $video->user->increment('balance', $amount);

                // Update total earnings video
                $video->increment('total_earnings', $amount);

                // Tandai views sebagai sudah dihitung
                VideoView::where('video_id', $video->id)
                    ->where('is_counted', false)
                    ->whereDate('created_at', '<=', $date)
                    ->update(['is_counted' => true]);
            });

            $results[] = [
                'video_id' => $video->id,
                'views' => $item->views_count,
                'amount' => $amount,
            ];
        }

        return $results;
    }
}

// app/Services/MembershipService.php

namespace App\Services;

use App\Models\Membership;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    private array $plans = [
        'monthly' => ['price' => 20000, 'duration' => 30],
        'yearly' => ['price' => 199000, 'duration' => 365],
    ];

    public function getPlans(): array
    {
        return $this->plans;
    }

    public function getPlanPrice(string $plan): int
    {
        return $this->plans[$plan]['price'] ?? 0;
    }

    public function getPlanDuration(string $plan): int
    {
        return $this->plans[$plan]['duration'] ?? 0;
    }

    /**
     * Validasi voucher
     */
    public function validateVoucher(string $code, User $user, float $amount): ?array
    {
        $voucher = Voucher::where('code', $code)->first();

        if (! $voucher || ! $voucher->canBeUsed()) {
            return null;
        }

        // Cek apakah user sudah pernah pakai voucher ini
        $usedByUser = VoucherUsage::where('voucher_id', $voucher->id)
            ->where('user_id', $user->id)
            ->count();

        if ($usedByUser >= $voucher->max_uses_per_user) {
            return null;
        }

        // Cek minimum purchase
        if ($amount < $voucher->min_purchase) {
            return null;
        }

        $discount = $voucher->calculateDiscount($amount);

        // Apply max discount jika ada
        if ($voucher->max_discount && $discount > $voucher->max_discount) {
            $discount = $voucher->max_discount;
        }

        return [
            'voucher' => $voucher,
            'discount' => $discount,
            'final_price' => max(0, $amount - $discount),
        ];
    }

    /**
     * Aktivasi membership
     */
    public function activateMembership(User $user, string $plan, ?string $voucherCode = null): Membership
    {
        $originalPrice = $this->getPlanPrice($plan);
        $duration = $this->getPlanDuration($plan);
        $discount = 0;
        $voucher = null;

        if ($voucherCode) {
            $voucherData = $this->validateVoucher($voucherCode, $user, $originalPrice);
            if ($voucherData) {
                $voucher = $voucherData['voucher'];
                $discount = $voucherData['discount'];
            }
        }

        $finalPrice = $originalPrice - $discount;

        return DB::transaction(function () use ($user, $plan, $originalPrice, $discount, $finalPrice, $duration, $voucher) {
            // Extend jika sudah member
            $startsAt = $user->hasActiveMembership()
                ? $user->member_until
                : now();

            $expiresAt = $startsAt->copy()->addDays($duration);

            $membership = Membership::create([
                'user_id' => $user->id,
                'plan' => $plan,
                'original_price' => $originalPrice,
                'discount' => $discount,
                'final_price' => $finalPrice,
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'status' => 'active',
            ]);

            // Update user
            $user->update([
                'is_member' => true,
                'member_until' => $expiresAt,
            ]);

            // Record voucher usage
            if ($voucher) {
                $voucher->increment('used_count');
                VoucherUsage::create([
                    'voucher_id' => $voucher->id,
                    'user_id' => $user->id,
                    'membership_id' => $membership->id,
                    'discount_amount' => $discount,
                    'used_at' => now(),
                ]);
            }

            return $membership;
        });
    }
}

// ============================================================================
// BAGIAN 7: CONTROLLERS
// ============================================================================

// app/Http/Controllers/VideoController.php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoRequest;
use App\Models\AdConfig;
use App\Models\Video;
use App\Services\StreamingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    public function __construct(
        private StreamingService $streamingService
    ) {}

    /**
     * Daftar video publik
     */
    public function index()
    {
        $videos = Video::where('status', 'ready')
            ->where('is_public', true)
            ->with('user:id,name')
            ->latest()
            ->paginate(20);

        return view('videos.index', compact('videos'));
    }

    /**
     * Form upload video
     */
    public function create()
    {
        $this->authorize('create', Video::class);

        return view('videos.create');
    }

    /**
     * Upload video baru
     */
    public function store(StoreVideoRequest $request)
    {
        $videoFile = $request->file('video');

        // Generate nama file unik
        $filename = Str::random(40).'.'.$videoFile->getClientOriginalExtension();

        // Simpan ke folder private (di luar public)
        $videoFile->storeAs('private/videos', $filename);

        $video = Video::create([
            'user_id' => auth()->id(),
            'title' => $request->title,
            'description' => $request->description,
            'filename' => $filename,
            'original_name' => $videoFile->getClientOriginalName(),
            'mime_type' => $videoFile->getMimeType(),
            'file_size' => $videoFile->getSize(),
            'is_public' => $request->boolean('is_public', true),
            'status' => 'ready',
        ]);

        // Handle thumbnail
        if ($request->hasFile('thumbnail')) {
            $thumbFile = $request->file('thumbnail');
            $thumbName = $video->slug.'.'.$thumbFile->getClientOriginalExtension();
            $thumbFile->storeAs('public/thumbnails', $thumbName);
            $video->update(['thumbnail' => $thumbName]);
        }

        return redirect()->route('videos.show', $video)
            ->with('success', 'Video berhasil diupload!');
    }

    /**
     * Halaman tonton video
     * URL: domain.com/v/{slug}
     */
    public function show(Video $video)
    {
        if (! $video->isReady()) {
            abort(404, 'Video tidak tersedia');
        }

        $video->load('user:id,name');

        // Cek apakah user punya akses tanpa iklan
        $skipAds = auth()->check() && (
            auth()->user()->hasActiveMembership() ||
            $video->user_id === auth()->id() ||
            auth()->user()->isAdmin()
        );

        $ads = [];
        if (! $skipAds) {
            $ads = AdConfig::where('is_active', true)
                ->orderBy('priority', 'desc')
                ->get();
        }

        return view('videos.show', compact('video', 'skipAds', 'ads'));
    }

    /**
     * Generate token untuk streaming
     */
    public function generateToken(Video $video, Request $request)
    {
        if (! $video->isReady()) {
            return response()->json(['error' => 'Video tidak tersedia'], 404);
        }

        $token = $this->streamingService->generateToken($video, $request);

        return response()->json([
            'token' => $token,
            'expires_in' => 1800, // 30 menit
            'stream_url' => route('stream.video', $token),
        ]);
    }

    /**
     * Hapus video
     */
    public function destroy(Video $video)
    {
        $this->authorize('delete', $video);

        // Hapus file video
        Storage::delete('private/videos/'.$video->filename);

        // Hapus thumbnail
        if ($video->thumbnail) {
            Storage::delete('public/thumbnails/'.$video->thumbnail);
        }

        $video->delete();

        return redirect()->route('dashboard')
            ->with('success', 'Video berhasil dihapus!');
    }
}

// app/Http/Controllers/StreamController.php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoToken;
use App\Services\StreamingService;
use Illuminate\Http\Request;

class StreamController extends Controller
{
    public function __construct(
        private StreamingService $streamingService
    ) {}

    /**
     * Stream video dengan token
     * Middleware: ValidateVideoToken sudah validasi
     */
    public function stream(Request $request, string $token)
    {
        $videoToken = $request->attributes->get('videoToken');
        $video = $request->attributes->get('video');

        if (! file_exists($video->getStoragePath())) {
            abort(404, 'Video file not found');
        }

        // Catat view (hanya sekali per session)
        $this->streamingService->recordView($video, $request);

        // Stream video dengan Range support
        return $this->streamingService->streamVideo($video, $request);
    }

    /**
     * Konfirmasi iklan sudah ditonton
     */
    public function confirmAdWatched(Request $request, string $token)
    {
        $videoToken = VideoToken::find($token);

        if (! $videoToken || ! $videoToken->isValid($request->ip(), $request->session()->getId())) {
            return response()->json(['error' => 'Invalid token'], 403);
        }

        $videoToken->markAdWatched();

        return response()->json(['success' => true]);
    }
}
