<?php

namespace App\Services;

use App\Models\Video;
use App\Models\VideoView;
use App\Models\Earning;
use App\Models\AdConfig;
use Illuminate\Support\Facades\DB;

class EarningService
{
    /**
     * Hitung penghasilan harian dari views
     */
    public function calculateDailyEarnings(string $date = null): array
    {
        $date = $date ?? now()->subDay()->toDateString();
        
        // Ambil CPM rate rata-rata dari iklan aktif
        $cpmRate = AdConfig::getAverageCpm();
        
        $results = [];
        
        // Ambil views yang belum dihitung (bukan member views)
        $viewsByVideo = VideoView::where('is_counted', false)
            ->where('is_member_view', false)
            ->whereDate('created_at', '<=', $date)
            ->groupBy('video_id')
            ->selectRaw('video_id, count(*) as views_count')
            ->get();
        
        foreach ($viewsByVideo as $item) {
            $video = Video::with('user')->find($item->video_id);
            if (!$video || !$video->user) continue;
            
            // Hitung penghasilan: (views / 1000) * CPM
            $amount = ($item->views_count / 1000) * $cpmRate;
            
            DB::transaction(function() use ($video, $item, $amount, $cpmRate, $date) {
                // Buat atau update record earning
                Earning::updateOrCreate(
                    ['video_id' => $video->id, 'calculation_date' => $date],
                    [
                        'user_id' => $video->user_id,
                        'views_count' => $item->views_count,
                        'cpm_rate' => $cpmRate,
                        'amount' => $amount,
                    ]
                );
                
                // Update balance user
                $video->user->increment('balance', $amount);
                
                // Update total earnings video
                $video->increment('total_earnings', $amount);
                
                // Tandai views sebagai sudah dihitung
                VideoView::where('video_id', $video->id)
                    ->where('is_counted', false)
                    ->whereDate('created_at', '<=', $date)
                    ->update(['is_counted' => true]);
            });
            
            $results[] = [
                'video_id' => $video->id,
                'title' => $video->title,
                'views' => $item->views_count,
                'amount' => $amount
            ];
        }
        
        return $results;
    }

    /**
     * Get earnings summary for a user
     */
    public function getUserEarningsSummary(int $userId, int $days = 30): array
    {
        $startDate = now()->subDays($days)->toDateString();
        
        $earnings = Earning::where('user_id', $userId)
            ->where('calculation_date', '>=', $startDate)
            ->selectRaw('calculation_date, SUM(amount) as total, SUM(views_count) as views')
            ->groupBy('calculation_date')
            ->orderBy('calculation_date')
            ->get();
        
        return [
            'total_amount' => $earnings->sum('total'),
            'total_views' => $earnings->sum('views'),
            'daily_data' => $earnings->toArray(),
        ];
    }
}
