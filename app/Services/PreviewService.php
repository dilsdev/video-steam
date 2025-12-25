<?php

namespace App\Services;

use App\Models\Video;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PreviewService
{
    protected string $ffmpegPath;
    protected string $ffprobePath;
    protected int $previewDuration;
    protected int $previewQuality;

    public function __construct()
    {
        $this->ffmpegPath = config('streaming.ffmpeg_path', 'ffmpeg');
        $this->ffprobePath = config('streaming.ffprobe_path', 'ffprobe');
        $this->previewDuration = config('streaming.preview_duration', 10);
        $this->previewQuality = config('streaming.preview_quality', 23);
    }

    /**
     * Generate preview dari 10 detik pertama video
     */
    public function generatePreview(Video $video): bool
    {
        $sourcePath = $video->getStoragePath();
        
        if (!file_exists($sourcePath)) {
            Log::error("PreviewService: Source video not found: {$sourcePath}");
            return false;
        }

        // Create previews directory if not exists
        $previewDir = storage_path('app/private/previews');
        if (!is_dir($previewDir)) {
            mkdir($previewDir, 0755, true);
        }

        $previewFilename = pathinfo($video->filename, PATHINFO_FILENAME) . '_preview.mp4';
        $outputPath = $previewDir . '/' . $previewFilename;

        // Get video duration first
        $duration = $this->getVideoDuration($sourcePath);
        $cutDuration = min($this->previewDuration, $duration);

        // FFmpeg command to extract first 10 seconds
        // -ss 0: start from beginning
        // -t {duration}: duration to extract
        // -c:v libx264: re-encode with H.264 for broad compatibility
        // -crf: quality (23 is default, lower = better)
        // -preset fast: encoding speed
        // -c:a aac: audio codec
        // -y: overwrite output
        $command = sprintf(
            '"%s" -y -i "%s" -ss 0 -t %d -c:v libx264 -crf %d -preset fast -c:a aac -b:a 128k -movflags +faststart "%s" 2>&1',
            $this->ffmpegPath,
            $sourcePath,
            $cutDuration,
            $this->previewQuality,
            $outputPath
        );

        Log::info("PreviewService: Running FFmpeg command", ['command' => $command]);

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            Log::error("PreviewService: FFmpeg failed", [
                'return_code' => $returnCode,
                'output' => implode("\n", array_slice($output, -20)) // Last 20 lines
            ]);
            return false;
        }

        if (!file_exists($outputPath)) {
            Log::error("PreviewService: Preview file not created: {$outputPath}");
            return false;
        }

        // Update video record with preview filename
        $video->update(['preview_filename' => $previewFilename]);

        Log::info("PreviewService: Preview generated successfully", [
            'video_id' => $video->id,
            'preview_file' => $previewFilename,
            'duration' => $cutDuration
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

        return $duration > 0 ? $duration : 60; // Default to 60 if can't detect
    }

    /**
     * Get full path to preview file
     */
    public function getPreviewPath(Video $video): string
    {
        if ($video->preview_filename) {
            return storage_path('app/private/previews/' . $video->preview_filename);
        }

        return '';
    }

    /**
     * Check if video has preview
     */
    public function hasPreview(Video $video): bool
    {
        if (!$video->preview_filename) {
            return false;
        }

        return file_exists($this->getPreviewPath($video));
    }

    /**
     * Delete preview file
     */
    public function deletePreview(Video $video): bool
    {
        if (!$this->hasPreview($video)) {
            return true;
        }

        $path = $this->getPreviewPath($video);
        
        if (file_exists($path)) {
            unlink($path);
        }

        $video->update(['preview_filename' => null]);

        return true;
    }
}
