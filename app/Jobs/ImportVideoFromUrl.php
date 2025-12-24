<?php

namespace App\Jobs;

use App\Models\Video;
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
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Determine filename and extension
            $pathInfo = pathinfo(parse_url($this->url, PHP_URL_PATH));
            $extension = strtolower($pathInfo['extension'] ?? 'mp4');
            $originalName = $pathInfo['basename'] ?? 'video.mp4';

            // Allow only specific video extensions
            if (! in_array($extension, ['mp4', 'mov', 'avi', 'webm', 'mkv'])) {
                $extension = 'mp4';
            }

            $filename = Str::random(40).'.'.$extension;

            // Use stream to handle large files better
            $tempPath = tempnam(sys_get_temp_dir(), 'video_import_');
            $handle = fopen($tempPath, 'w');

            $response = Http::timeout(600)->withOptions([
                'verify' => false,
                'sink' => $handle,
            ])->get($this->url);

            fclose($handle);

            if (! $response->successful()) {
                unlink($tempPath);
                throw new \Exception("Failed to download from {$this->url}: ".$response->status());
            }

            // Move to storage
            $fileContent = file_get_contents($tempPath);
            Storage::put("private/videos/{$filename}", $fileContent);
            $size = filesize($tempPath);

            unlink($tempPath);

            // Create video record
            Video::create([
                'user_id' => $this->userId,
                'title' => $originalName,
                'description' => "Imported from {$this->url}",
                'filename' => $filename,
                'thumbnail' => null, // Use default
                'original_name' => $originalName,
                'mime_type' => 'video/'.$extension,
                'file_size' => $size,
                'is_public' => true,
                'status' => 'ready',
            ]);

            \Log::info("Successfully imported video from {$this->url}");

        } catch (\Exception $e) {
            \Log::error("Job Import failed for {$this->url}: ".$e->getMessage());
            $this->fail($e);
        }
    }
}
