<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Video;
use App\Models\VideoView;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // Get the uploader user
        $uploader = User::where('email', 'uploader@example.com')->first();

        if (! $uploader) {
            $this->command->error('Uploader user not found! Run DatabaseSeeder first.');

            return;
        }

        // Create demo videos
        $videosData = [
            ['title' => 'Tutorial Laravel untuk Pemula', 'views' => 5000],
            ['title' => 'Cara Membuat Website dengan PHP', 'views' => 3000],
            ['title' => 'Tips dan Trik Programming', 'views' => 2000],
        ];

        foreach ($videosData as $data) {
            // Create video
            $video = Video::create([
                'user_id' => $uploader->id,
                'slug' => Str::lower(Str::random(8)),
                'title' => $data['title'],
                'description' => 'Demo video untuk testing earnings',
                'filename' => 'demo-'.Str::random(10).'.mp4',
                'original_name' => Str::slug($data['title']).'.mp4',
                'mime_type' => 'video/mp4',
                'file_size' => rand(50000000, 200000000),
                'duration' => rand(300, 1800),
                'status' => 'ready',
                'total_views' => $data['views'],
                'total_earnings' => 0,
                'is_public' => true,
            ]);

            // Create unique views (simplified - batch insert)
            $views = [];
            for ($i = 0; $i < $data['views']; $i++) {
                $views[] = [
                    'video_id' => $video->id,
                    'user_id' => null,
                    'ip_address' => rand(1, 255).'.'.rand(1, 255).'.'.rand(1, 255).'.'.rand(1, 255),
                    'session_id' => Str::random(40),
                    'user_agent' => 'Mozilla/5.0 Demo',
                    'is_member_view' => false,
                    'is_counted' => false,
                    'created_at' => now()->subDays(2),
                ];

                // Insert in batches of 500
                if (count($views) >= 500) {
                    VideoView::insert($views);
                    $views = [];
                }
            }

            // Insert remaining views
            if (! empty($views)) {
                VideoView::insert($views);
            }
        }
    }
}
