<?php

namespace App\Jobs;

use App\Models\Video;
use App\Services\PreviewService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateVideoPreview implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Video $video
    ) {}

    /**
     * Execute the job.
     */
    public function handle(PreviewService $previewService): void
    {
        Log::info("GenerateVideoPreview: Starting for video {$this->video->id}");

        if (!$this->video->hasFile()) {
            Log::warning("GenerateVideoPreview: Video file not found for video {$this->video->id}");
            return;
        }

        $success = $previewService->generatePreview($this->video);

        if ($success) {
            Log::info("GenerateVideoPreview: Completed for video {$this->video->id}");
        } else {
            Log::error("GenerateVideoPreview: Failed for video {$this->video->id}");
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateVideoPreview: Job failed for video {$this->video->id}", [
            'error' => $exception->getMessage()
        ]);
    }
}
