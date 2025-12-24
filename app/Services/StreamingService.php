<?php

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

                // Validasi range
                if ($start > $end || $start >= $fileSize) {
                    abort(416, 'Requested Range Not Satisfiable');
                }

                $length = $end - $start + 1;
                $statusCode = 206; // Partial Content
            }
        }

        $headers = [
            'Content-Type' => $mimeType,
            'Content-Length' => $length,
            'Accept-Ranges' => 'bytes',
            'Content-Range' => "bytes {$start}-{$end}/{$fileSize}",
            'Cache-Control' => 'private, max-age=2592000', // Allow caching for performance
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
        ];

        return response()->stream(function () use ($path, $start, $length) {
            // Disable timeout for long downloads
            set_time_limit(0);
            
            // Close session to prevent locking other requests
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            // Clean output buffer to prevent memory issues
            if (ob_get_level()) {
                ob_end_clean();
            }

            $stream = fopen($path, 'rb');

            if ($stream === false) {
                return;
            }

            fseek($stream, $start);

            $remaining = $length;
            $bufferSize = 1024 * 512; // Increased to 512KB for better throughput
            
            while (! feof($stream) && $remaining > 0) {
                // Check if connection is lost before reading
                if (connection_aborted()) {
                    fclose($stream);
                    return;
                }

                $readSize = min($bufferSize, $remaining);
                $data = fread($stream, $readSize);

                if ($data === false) {
                    break;
                }

                echo $data;
                $remaining -= strlen($data);
                
                // Flush system output buffer
                flush();
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
