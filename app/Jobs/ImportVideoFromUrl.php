<?php

namespace App\Jobs;

use App\Models\Video;
use App\Services\ThumbnailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportVideoFromUrl implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 1200; // 20 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $url,
        public int $userId
    ) {}

    /**
     * Execute the job - now uses external_url directly without downloading
     */
    public function handle(ThumbnailService $thumbnailService): void
    {
        try {
            // Determine filename from URL
            $pathInfo = pathinfo(parse_url($this->url, PHP_URL_PATH));
            $extension = strtolower($pathInfo['extension'] ?? 'mp4');
            $originalName = $pathInfo['basename'] ?? 'video.mp4';

            // Allow only specific video extensions
            if (! in_array($extension, ['mp4', 'mov', 'avi', 'webm', 'mkv'])) {
                $extension = 'mp4';
            }

            // Create video record with external_url - NO DOWNLOAD needed
            $video = Video::create([
                'user_id' => $this->userId,
                'title' => pathinfo($originalName, PATHINFO_FILENAME), // Use filename without extension as title
                'description' => '-',
                'filename' => null, // No local file
                'external_url' => $this->url, // Store external URL directly
                'original_name' => $originalName,
                'mime_type' => 'video/' . $extension,
                'file_size' => 0, // Unknown for external
                'is_public' => true,
                'status' => 'ready',
            ]);

            \Log::info("Successfully imported video from {$this->url} using external URL (no download)");

        } catch (\Exception $e) {
            \Log::error("Job Import failed for {$this->url}: " . $e->getMessage());
            $this->fail($e);
        }
    }
}
