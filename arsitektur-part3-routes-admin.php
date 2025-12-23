<?php

/**
 * ARSITEKTUR VIDEO PLATFORM - BAGIAN 3: DASHBOARD, ADMIN, ROUTES & VIEWS
 */

// ============================================================================
// BAGIAN 8: DASHBOARD & PAYOUT CONTROLLERS
// ============================================================================

// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use App\Models\Earning;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $videos = $user->videos()
            ->withCount('views')
            ->latest()
            ->get();

        $stats = [
            'total_videos' => $videos->count(),
            'total_views' => $videos->sum('total_views'),
            'total_earnings' => $user->earnings()->sum('amount'),
            'balance' => $user->balance,
            'pending_payouts' => $user->payouts()->where('status', 'pending')->sum('amount'),
        ];

        $recentEarnings = Earning::where('user_id', $user->id)
            ->with('video:id,title,slug')
            ->latest()
            ->take(10)
            ->get();

        $earningsChart = Earning::where('user_id', $user->id)
            ->where('calculation_date', '>=', now()->subDays(30))
            ->groupBy('calculation_date')
            ->selectRaw('calculation_date, sum(amount) as total')
            ->orderBy('calculation_date')
            ->get();

        return view('dashboard', compact('videos', 'stats', 'recentEarnings', 'earningsChart'));
    }
}

// app/Http/Controllers/PayoutController.php

namespace App\Http\Controllers;

use App\Http\Requests\StorePayoutRequest;
use App\Models\Payout;
use App\Models\Setting;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = auth()->user()->payouts()->latest()->paginate(20);
        $minPayout = Setting::get('min_payout', 100000);
        $payoutFee = Setting::get('payout_fee', 2500);

        return view('payouts.index', compact('payouts', 'minPayout', 'payoutFee'));
    }

    public function store(StorePayoutRequest $request)
    {
        $user = auth()->user();
        $fee = Setting::get('payout_fee', 2500);
        $netAmount = $request->amount - $fee;

        Payout::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'fee' => $fee,
            'net_amount' => $netAmount,
            'payment_method' => $request->payment_method,
            'payment_account' => $request->payment_account,
            'payment_name' => $request->payment_name,
            'status' => 'pending',
        ]);

        $user->decrement('balance', $request->amount);

        return redirect()->route('payouts.index')
            ->with('success', 'Permintaan penarikan berhasil diajukan!');
    }
}

// app/Http/Controllers/MembershipController.php

namespace App\Http\Controllers;

use App\Services\MembershipService;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function __construct(
        private MembershipService $membershipService
    ) {}

    public function index()
    {
        $plans = $this->membershipService->getPlans();
        $currentMembership = auth()->user()->memberships()
            ->where('status', 'active')
            ->latest()
            ->first();

        return view('memberships.index', compact('plans', 'currentMembership'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:monthly,yearly',
            'voucher_code' => 'nullable|string|max:50',
        ]);

        $membership = $this->membershipService->activateMembership(
            auth()->user(),
            $request->plan,
            $request->voucher_code
        );

        return redirect()->route('home')
            ->with('success', 'Membership berhasil diaktifkan hingga '.$membership->expires_at->format('d M Y'));
    }

    public function validateVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'plan' => 'required|in:monthly,yearly',
        ]);

        $amount = $this->membershipService->getPlanPrice($request->plan);
        $result = $this->membershipService->validateVoucher(
            $request->code,
            auth()->user(),
            $amount
        );

        if (! $result) {
            return response()->json(['valid' => false, 'message' => 'Voucher tidak valid']);
        }

        return response()->json([
            'valid' => true,
            'discount' => $result['discount'],
            'final_price' => $result['final_price'],
            'voucher_type' => $result['voucher']->type,
            'voucher_value' => $result['voucher']->value,
        ]);
    }
}

// ============================================================================
// BAGIAN 9: ADMIN CONTROLLERS
// ============================================================================

// app/Http/Controllers/Admin/AdminDashboardController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Earning;
use App\Models\Payout;
use App\Models\User;
use App\Models\Video;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_uploaders' => User::where('role', 'uploader')->count(),
            'total_members' => User::where('is_member', true)->count(),
            'total_videos' => Video::count(),
            'total_views' => Video::sum('total_views'),
            'total_earnings_paid' => Earning::sum('amount'),
            'pending_payouts' => Payout::where('status', 'pending')->sum('amount'),
            'pending_payouts_count' => Payout::where('status', 'pending')->count(),
        ];

        $recentPayouts = Payout::with('user:id,name,email')
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentPayouts'));
    }
}

// app/Http/Controllers/Admin/PayoutManagementController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use Illuminate\Http\Request;

class PayoutManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Payout::with('user:id,name,email');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $payouts = $query->latest()->paginate(20);

        return view('admin.payouts.index', compact('payouts'));
    }

    public function process(Request $request, Payout $payout)
    {
        $request->validate([
            'status' => 'required|in:processing,completed,failed,cancelled',
            'admin_notes' => 'nullable|string|max:500',
            'proof_file' => 'nullable|file|max:2048',
        ]);

        $data = [
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'processed_by' => auth()->id(),
            'processed_at' => now(),
        ];

        if ($request->hasFile('proof_file')) {
            $file = $request->file('proof_file');
            $filename = 'payout-'.$payout->id.'.'.$file->getClientOriginalExtension();
            $file->storeAs('private/payouts', $filename);
            $data['proof_file'] = $filename;
        }

        // Jika cancelled atau failed, kembalikan balance
        if (in_array($request->status, ['cancelled', 'failed'])) {
            $payout->user->increment('balance', $payout->amount);
        }

        $payout->update($data);

        return redirect()->route('admin.payouts.index')
            ->with('success', 'Payout berhasil diproses!');
    }
}

// ============================================================================
// BAGIAN 10: ROUTES
// ============================================================================

// routes/web.php
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PayoutController;
use App\Http\Controllers\StreamController;
use App\Http\Controllers\VideoController;

// Public routes
Route::get('/', [VideoController::class, 'index'])->name('home');
Route::get('/v/{video:slug}', [VideoController::class, 'show'])->name('videos.show');

// Streaming (dengan middleware khusus)
Route::middleware(['throttle:streaming'])->group(function () {
    Route::get('/stream/{token}', [StreamController::class, 'stream'])
        ->name('stream.video')
        ->middleware('validate.video.token');
    Route::post('/stream/{token}/ad-watched', [StreamController::class, 'confirmAdWatched'])
        ->name('stream.ad-watched');
});

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Video token generation
    Route::post('/videos/{video}/token', [VideoController::class, 'generateToken'])
        ->name('videos.token');

    // Membership
    Route::get('/memberships', [MembershipController::class, 'index'])->name('memberships.index');
    Route::post('/memberships', [MembershipController::class, 'store'])->name('memberships.store');
    Route::post('/memberships/validate-voucher', [MembershipController::class, 'validateVoucher'])
        ->name('memberships.validate-voucher');
});

// Uploader routes
Route::middleware(['auth', 'uploader'])->prefix('uploader')->name('uploader.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('videos', VideoController::class)->except(['index', 'show']);
    Route::get('/payouts', [PayoutController::class, 'index'])->name('payouts.index');
    Route::post('/payouts', [PayoutController::class, 'store'])->name('payouts.store');
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/payouts', [PayoutManagementController::class, 'index'])->name('payouts.index');
    Route::post('/payouts/{payout}/process', [PayoutManagementController::class, 'process'])
        ->name('payouts.process');
});

// ============================================================================
// BAGIAN 11: CONSOLE COMMANDS (CRON)
// ============================================================================

// app/Console/Commands/CalculateEarnings.php

namespace App\Console\Commands;

use App\Services\EarningService;
use Illuminate\Console\Command;

class CalculateEarnings extends Command
{
    protected $signature = 'earnings:calculate {--date=}';

    protected $description = 'Calculate video earnings from views';

    public function handle(EarningService $earningService)
    {
        $date = $this->option('date') ?? now()->subDay()->toDateString();

        $this->info("Calculating earnings for: {$date}");

        $results = $earningService->calculateDailyEarnings($date);

        $this->info('Processed '.count($results).' videos');

        foreach ($results as $result) {
            $this->line("  Video #{$result['video_id']}: {$result['views']} views = Rp ".number_format($result['amount']));
        }

        return Command::SUCCESS;
    }
}

// app/Console/Commands/CleanExpiredTokens.php

namespace App\Console\Commands;

use App\Models\VideoToken;
use Illuminate\Console\Command;

class CleanExpiredTokens extends Command
{
    protected $signature = 'tokens:clean';

    protected $description = 'Clean expired video tokens';

    public function handle()
    {
        $deleted = VideoToken::where('expires_at', '<', now())->delete();
        $this->info("Deleted {$deleted} expired tokens");

        return Command::SUCCESS;
    }
}

// app/Console/Commands/RefreshMembershipStatus.php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class RefreshMembershipStatus extends Command
{
    protected $signature = 'memberships:refresh';

    protected $description = 'Refresh expired membership status';

    public function handle()
    {
        $updated = User::where('is_member', true)
            ->where('member_until', '<', now())
            ->update(['is_member' => false]);

        $this->info("Updated {$updated} expired memberships");

        return Command::SUCCESS;
    }
}

// Daftar di routes/console.php (Laravel 12)
use Illuminate\Support\Facades\Schedule;

Schedule::command('earnings:calculate')->dailyAt('01:00');
Schedule::command('tokens:clean')->hourly();
Schedule::command('memberships:refresh')->dailyAt('00:00');

// ============================================================================
// BAGIAN 12: CPANEL CRON SETUP
// ============================================================================

/**
 * Di cPanel -> Cron Jobs, tambahkan:
 *
 * Setiap menit (untuk Laravel scheduler):
 * * * * * * cd /home/username/public_html && php artisan schedule:run >> /dev/null 2>&1
 *
 * Atau jalankan manual per command:
 * 0 1 * * * cd /home/username/public_html && php artisan earnings:calculate
 * 0 * * * * cd /home/username/public_html && php artisan tokens:clean
 * 0 0 * * * cd /home/username/public_html && php artisan memberships:refresh
 */

// ============================================================================
// BAGIAN 13: SECURITY CONFIGURATION
// ============================================================================

// storage/app/private/.htaccess
// Deny from all

// storage/app/private/videos/.htaccess
// Deny from all

// config/cors.php (batasi origin)
return [
    'paths' => ['api/*', 'stream/*'],
    'allowed_methods' => ['GET', 'POST'],
    'allowed_origins' => [env('APP_URL')],
    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization'],
    'max_age' => 0,
    'supports_credentials' => true,
];

// bootstrap/app.php - Rate Limiting
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('streaming', function ($request) {
    return Limit::perMinute(100)->by($request->ip());
});

RateLimiter::for('api', function ($request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

// ============================================================================
// BAGIAN 14: ENV CONFIGURATION
// ============================================================================

/**
 * .env untuk Shared Hosting
 *
 * APP_ENV=production
 * APP_DEBUG=false
 * APP_URL=https://domain.com
 *
 * DB_CONNECTION=mysql
 * DB_HOST=localhost
 * DB_PORT=3306
 * DB_DATABASE=your_database
 * DB_USERNAME=your_username
 * DB_PASSWORD=your_password
 *
 * # Shared hosting tidak punya Redis
 * CACHE_DRIVER=file
 * QUEUE_CONNECTION=database
 * SESSION_DRIVER=database
 *
 * # Email (gunakan SMTP dari hosting)
 * MAIL_MAILER=smtp
 * MAIL_HOST=mail.domain.com
 * MAIL_PORT=587
 *
 * # Membership pricing (dalam rupiah)
 * MEMBERSHIP_MONTHLY_PRICE=50000
 * MEMBERSHIP_YEARLY_PRICE=500000
 *
 * # Payout settings
 * MIN_PAYOUT=100000
 * PAYOUT_FEE=2500
 *
 * # CPM Rate (per 1000 views)
 * DEFAULT_CPM_RATE=2.00
 */
