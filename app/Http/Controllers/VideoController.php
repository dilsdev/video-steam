<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateVideoThumbnail;
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
     * Daftar video publik (Homepage)
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
     * Form upload video (uploader only)
     */
    public function create()
    {
        return view('videos.create');
    }

    /**
     * Upload video baru
     */
    public function store(Request $request)
    {
        $maxVideoSizeKb = \App\Models\Setting::get('max_video_size_mb', 500) * 1024;

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'video' => "required|file|mimes:mp4,mov,avi,webm,mkv|max:{$maxVideoSizeKb}",
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'auto_thumbnail' => 'nullable|string', // Base64 data URL from JavaScript
            'is_public' => 'boolean',
        ]);

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

        // Handle thumbnail - prioritas: manual upload > auto-generated > default
        if ($request->hasFile('thumbnail')) {
            // Manual file upload - gunakan path langsung untuk konsistensi
            $thumbFile = $request->file('thumbnail');
            $thumbName = $video->slug.'.'.$thumbFile->getClientOriginalExtension();

            // Ensure thumbnails directory exists
            $thumbnailDir = storage_path('app/public/thumbnails');
            if (! is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            // Move file ke lokasi thumbnails
            $thumbFile->move($thumbnailDir, $thumbName);
            $video->update(['thumbnail' => $thumbName]);

            \Log::info("Manual thumbnail saved: {$thumbName}");
        } elseif ($request->filled('auto_thumbnail')) {
            // Auto-generated thumbnail from JavaScript (base64)
            $this->saveBase64Thumbnail($video, $request->auto_thumbnail);
        }
        // Jika tidak ada manual/auto thumbnail, dispatch FFmpeg job untuk generate thumbnail otomatis
        if (!$video->thumbnail) {
            GenerateVideoThumbnail::dispatch($video);
        }

        return redirect()->route('videos.show', $video)
            ->with('success', 'Video berhasil diupload!' . (!$video->thumbnail ? ' Thumbnail sedang di-generate.' : ''));
    }

    /**
     * Update thumbnail from auto-generated base64
     */
    public function updateAutoThumbnail(Request $request, Video $video)
    {
        // Allow owner or admin
        if ($video->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'image' => 'required|string',
        ]);

        if ($this->saveBase64Thumbnail($video, $request->image)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['error' => 'Failed to save thumbnail'], 500);
    }

    /**
     * Save base64 thumbnail from JavaScript Canvas
     */
    private function saveBase64Thumbnail(Video $video, string $base64Data): bool
    {
        try {
            // Remove data URL prefix (data:image/jpeg;base64,)
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $matches)) {
                $extension = strtolower($matches[1]);
                $extension = $extension === 'jpeg' ? 'jpg' : $extension;
                
                // SECURITY: Whitelist allowed extensions
                if (!in_array($extension, ['jpg', 'png', 'webp'])) {
                    $extension = 'jpg';
                }
                
                // IMPORTANT: Strip the header
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
            } else {
                $extension = 'jpg';
            }

            // Decode base64
            $imageData = base64_decode($base64Data);
            
            if ($imageData === false) {
                return false;
            }

            // Ensure thumbnails directory exists
            $thumbnailDir = storage_path('app/public/thumbnails');
            if (!is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            // Save file
            $thumbName = $video->slug . '.' . $extension;
            $thumbnailPath = $thumbnailDir . '/' . $thumbName;
            
            if (file_put_contents($thumbnailPath, $imageData)) {
                $video->update(['thumbnail' => $thumbName]);
                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error("Error saving base64 thumbnail: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Preview video (direct file access for creating thumbnails)
     */
    public function preview(Video $video)
    {
        if (!$video->isReady()) {
            abort(404);
        }

        // SECURITY: Check privacy
        if (!$video->is_public) {
            $user = auth()->user();
            if (!$user || ($user->id !== $video->user_id && !$user->isAdmin())) {
                abort(403);
            }
        }

        $path = $video->getStoragePath();

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
            'Accept-Ranges' => 'bytes',
        ]);
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
            $ads = AdConfig::getActiveAds();
        }

        // Related videos
        $relatedVideos = Video::where('status', 'ready')
            ->where('is_public', true)
            ->where('id', '!=', $video->id)
            ->inRandomOrder()
            ->limit(6)
            ->get();

        return view('videos.show', compact('video', 'skipAds', 'ads', 'relatedVideos'));
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
            'expires_in' => 1800,
            'stream_url' => route('stream.video', $token),
        ]);
    }

    /**
     * Edit video
     */
    public function edit(Video $video)
    {
        if ($video->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('videos.edit', compact('video'));
    }

    /**
     * Update video
     */
    public function update(Request $request, Video $video)
    {
        if ($video->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'thumbnail' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_public' => 'boolean',
        ]);

        $video->update([
            'title' => $request->title,
            'description' => $request->description,
            'is_public' => $request->boolean('is_public', true),
        ]);

        if ($request->hasFile('thumbnail')) {
            // Delete old thumbnail
            if ($video->thumbnail) {
                $oldPath = storage_path('app/public/thumbnails/'.$video->thumbnail);
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $thumbFile = $request->file('thumbnail');
            $thumbName = $video->slug.'.'.$thumbFile->getClientOriginalExtension();

            // Ensure thumbnails directory exists
            $thumbnailDir = storage_path('app/public/thumbnails');
            if (! is_dir($thumbnailDir)) {
                mkdir($thumbnailDir, 0755, true);
            }

            // Move file ke lokasi thumbnails
            $thumbFile->move($thumbnailDir, $thumbName);
            $video->update(['thumbnail' => $thumbName]);
        }

        return redirect()->route('videos.show', $video)
            ->with('success', 'Video berhasil diupdate!');
    }

    /**
     * Hapus video
     */
    public function destroy(Video $video)
    {
        if ($video->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403);
        }

        // Hapus file video
        Storage::delete('private/videos/'.$video->filename);

        // Hapus thumbnail
        if ($video->thumbnail) {
            Storage::delete('public/thumbnails/'.$video->thumbnail);
        }

        $video->delete();

        return redirect()->route('uploader.dashboard')
            ->with('success', 'Video berhasil dihapus!');
    }
}
