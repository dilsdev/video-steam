<?php

namespace App\Console\Commands;

use App\Jobs\GenerateVideoThumbnail;
use App\Models\Video;
use App\Services\ThumbnailService;
use Illuminate\Console\Command;

class GenerateThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'video:generate-thumbnails 
                            {--video= : Generate thumbnail for specific video ID}
                            {--force : Regenerate even if thumbnail exists}
                            {--sync : Run synchronously instead of queuing}';

    /**
     * The console command description.
     */
    protected $description = 'Generate thumbnails for videos using FFmpeg (extracts frame at 10 seconds)';

    /**
     * Execute the console command.
     */
    public function handle(ThumbnailService $thumbnailService): int
    {
        $videoId = $this->option('video');
        $force = $this->option('force');
        $sync = $this->option('sync');

        // Build query
        $query = Video::where('status', 'ready');

        if ($videoId) {
            $query->where('id', $videoId);
        }

        if (!$force) {
            $query->whereNull('thumbnail');
        }

        $videos = $query->get();

        if ($videos->isEmpty()) {
            $this->info('No videos found to process.');
            return Command::SUCCESS;
        }

        $this->info("Found {$videos->count()} videos to process.");

        $bar = $this->output->createProgressBar($videos->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($videos as $video) {
            if (!$video->hasFile()) {
                $this->newLine();
                $this->warn("Skipping video #{$video->id}: File not found");
                $bar->advance();
                $failed++;
                continue;
            }

            if ($sync) {
                // Run synchronously
                try {
                    if ($thumbnailService->generateThumbnail($video)) {
                        $success++;
                        $this->newLine();
                        $this->info("Generated thumbnail for video #{$video->id}");
                    } else {
                        $failed++;
                        $this->newLine();
                        $this->error("Failed to generate thumbnail for video #{$video->id}");
                    }
                } catch (\Exception $e) {
                    $failed++;
                    $this->newLine();
                    $this->error("Error for video #{$video->id}: {$e->getMessage()}");
                }
            } else {
                // Queue the job
                GenerateVideoThumbnail::dispatch($video, $force);
                $success++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        if ($sync) {
            $this->info("Completed: {$success} successful, {$failed} failed");
        } else {
            $this->info("Queued {$success} videos for thumbnail generation.");
            $this->info("Run 'php artisan queue:work' to process the queue.");
        }

        return Command::SUCCESS;
    }
}
