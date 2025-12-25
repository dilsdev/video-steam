<?php

namespace App\Services;

use App\Models\Video;
use Illuminate\Support\Facades\Log;

class ThumbnailService
{
    protected string $ffmpegPath;
    protected string $ffprobePath;
    protected int $thumbnailTime;

    public function __construct()
    {
        $this->ffmpegPath = config('streaming.ffmpeg_path', 'ffmpeg');
        $this->ffprobePath = config('streaming.ffprobe_path', 'ffprobe');
        $this->thumbnailTime = config('streaming.thumbnail_time', 10); // Frame at 10 seconds
    }

    /**
     * Generate thumbnail image from video frame at specified time
     */
    public function generateThumbnail(Video $video): bool
    {
        $sourcePath = $video->getStoragePath();
        
        if (!file_exists($sourcePath)) {
            Log::error("ThumbnailService: Source video not found: {$sourcePath}");
            return false;
        }

        // Create thumbnails directory if not exists
        $thumbnailDir = storage_path('app/public/thumbnails');
        if (!is_dir($thumbnailDir)) {
            mkdir($thumbnailDir, 0755, true);
        }

        // Get video duration to check if we can get frame at specified time
        $duration = $this->getVideoDuration($sourcePath);
        $frameTime = min($this->thumbnailTime, max(1, $duration - 1)); // At least 1 second from start

        // For very short videos, use frame at 1/3 of duration
        if ($duration < $this->thumbnailTime) {
            $frameTime = max(1, $duration / 3);
        }

        $thumbnailFilename = $video->slug . '.jpg';
        $outputPath = $thumbnailDir . '/' . $thumbnailFilename;

        // FFmpeg command to extract single frame as JPEG
        // -ss {time}: seek to specific time
        // -vframes 1: extract only 1 frame
        // -q:v 2: JPEG quality (2-5 is good, lower = better)
        // -vf scale: scale to max 1280x720 while maintaining aspect ratio
        $command = sprintf(
            '"%s" -y -ss %f -i "%s" -vframes 1 -q:v 2 -vf "scale=1280:720:force_original_aspect_ratio=decrease" "%s" 2>&1',
            $this->ffmpegPath,
            $frameTime,
            $sourcePath,
            $outputPath
        );

        Log::info("ThumbnailService: Running FFmpeg command", ['command' => $command]);

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            Log::error("ThumbnailService: FFmpeg failed", [
                'return_code' => $returnCode,
                'output' => implode("\n", array_slice($output, -20))
            ]);
            return false;
        }

        if (!file_exists($outputPath)) {
            Log::error("ThumbnailService: Thumbnail file not created: {$outputPath}");
            return false;
        }

        // Update video record with thumbnail filename
        $video->update(['thumbnail' => $thumbnailFilename]);

        Log::info("ThumbnailService: Thumbnail generated successfully", [
            'video_id' => $video->id,
            'thumbnail' => $thumbnailFilename,
            'frame_time' => $frameTime
        ]);

        return true;
    }

    /**
     * Get video duration in seconds
     */
    public function getVideoDuration(string $path): float
    {
        $command = sprintf(
            '"%s" -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 "%s"',
            $this->ffprobePath,
            $path
        );

        $output = shell_exec($command);
        $duration = floatval(trim($output ?? '0'));

        return $duration > 0 ? $duration : 60;
    }

    /**
     * Check if video already has a thumbnail
     */
    public function hasThumbnail(Video $video): bool
    {
        if (!$video->thumbnail) {
            return false;
        }

        $path = storage_path('app/public/thumbnails/' . $video->thumbnail);
        return file_exists($path);
    }

    /**
     * Delete existing thumbnail
     */
    public function deleteThumbnail(Video $video): bool
    {
        if (!$this->hasThumbnail($video)) {
            return true;
        }

        $path = storage_path('app/public/thumbnails/' . $video->thumbnail);
        
        if (file_exists($path)) {
            unlink($path);
        }

        $video->update(['thumbnail' => null]);

        return true;
    }
}
