<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\Earning;
use App\Models\Setting;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard uploader
     */
    public function index()
    {
        $user = auth()->user();

        // Get paginated videos
        $videos = $user->videos()
            ->withCount('views')
            ->latest()
            ->paginate(10);

        // Get stats from all videos (not just paginated)
        $allVideos = $user->videos()->get();
        $minPayout = Setting::get('min_payout', 100000);

        $stats = [
            'total_videos' => $allVideos->count(),
            'total_views' => $allVideos->sum('total_views'),
            'total_earnings' => $user->earnings()->sum('amount'),
            'balance' => $user->balance,
            'pending_payouts' => $user->payouts()->where('status', 'pending')->sum('amount'),
            'min_payout' => $minPayout,
            'can_withdraw' => $user->balance >= $minPayout,
        ];

        $recentEarnings = Earning::where('user_id', $user->id)
            ->with('video:id,title,slug')
            ->latest('calculation_date')
            ->take(10)
            ->get();

        // Chart data - last 30 days
        $earningsChart = Earning::where('user_id', $user->id)
            ->where('calculation_date', '>=', now()->subDays(30))
            ->groupBy('calculation_date')
            ->selectRaw('calculation_date, SUM(amount) as total, SUM(views_count) as views')
            ->orderBy('calculation_date')
            ->get();

        return view('dashboard', compact('videos', 'stats', 'recentEarnings', 'earningsChart'));
    }
}
