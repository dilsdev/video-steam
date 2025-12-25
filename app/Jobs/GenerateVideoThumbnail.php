<?php

namespace App\Jobs;

use App\Models\Video;
use App\Services\ThumbnailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateVideoThumbnail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120; // 2 minutes - thumbnails are quick

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Video $video,
        public bool $forceRegenerate = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ThumbnailService $thumbnailService): void
    {
        Log::info("GenerateVideoThumbnail: Starting for video {$this->video->id}");

        if (!$this->video->hasFile()) {
            Log::warning("GenerateVideoThumbnail: Video file not found for video {$this->video->id}");
            return;
        }

        // Skip if thumbnail already exists (unless force regenerate)
        if (!$this->forceRegenerate && $thumbnailService->hasThumbnail($this->video)) {
            Log::info("GenerateVideoThumbnail: Thumbnail already exists for video {$this->video->id}");
            return;
        }

        $success = $thumbnailService->generateThumbnail($this->video);

        if ($success) {
            Log::info("GenerateVideoThumbnail: Completed for video {$this->video->id}");
        } else {
            Log::error("GenerateVideoThumbnail: Failed for video {$this->video->id}");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateVideoThumbnail: Job failed for video {$this->video->id}", [
            'error' => $exception->getMessage()
        ]);
    }
}
