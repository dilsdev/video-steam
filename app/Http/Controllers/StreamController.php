<?php

namespace App\Http\Controllers;

use App\Models\SecurityLog;
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
     */
    public function stream(Request $request, string $token)
    {
        $videoToken = VideoToken::with('video')->find($token);

        // Validate token
        if (! $videoToken) {
            SecurityLog::log('invalid_token', $request->ip(), null, 'Token not found: '.substr($token, 0, 10).'...');
            abort(403, 'Invalid token');
        }

        if (! $videoToken->isValid($request->ip(), $request->session()->getId())) {
            SecurityLog::log('expired_token', $request->ip(), $videoToken->user_id, 'Token expired or invalid');
            abort(403, 'Token expired or invalid');
        }

        $video = $videoToken->video;

        if (! $video || ! file_exists($video->getStoragePath())) {
            // Untuk development, return pesan yang jelas
            if (config('app.debug')) {
                abort(404, 'Video file not found at: '.$video->getStoragePath());
            }
            abort(404, 'Video file not found');
        }

        // Catat view (hanya sekali per session)
        $this->streamingService->recordView($video, $request);

        // Stream video dengan Nginx (atau fallback PHP)
        return $this->streamingService->streamVideoNginx($video, $request);
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
