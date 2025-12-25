<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Earning;
use App\Models\Membership;
use App\Models\Payout;
use App\Models\User;
use App\Models\Video;

class AdminDashboardController extends Controller
{
    /**
     * Admin dashboard
     */
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_uploaders' => User::where('role', 'uploader')->count(),
            'total_members' => User::where('is_member', true)->count(),
            'total_videos' => Video::count(),
            'total_views' => Video::sum('total_views'),
            'total_earnings_paid' => Earning::sum('amount'),
            'pending_payouts' => Payout::where('status', 'pending')->sum('amount'),
            'pending_payouts_count' => Payout::where('status', 'pending')->count(),
            'completed_payouts' => Payout::where('status', 'completed')->sum('net_amount'),
            'total_memberships' => Membership::count(),
            'active_memberships' => Membership::where('status', 'active')
                ->where('expires_at', '>', now())
                ->count(),
        ];

        $recentPayouts = Payout::with('user:id,name,email')
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get();

        $recentUsers = User::latest()->take(10)->get();

        $earningsChart = Earning::where('calculation_date', '>=', now()->subDays(30))
            ->groupBy('calculation_date')
            ->selectRaw('calculation_date, SUM(amount) as total')
            ->orderBy('calculation_date')
            ->get();

        // Get recent videos
        $recentVideos = Video::with('user')
            ->latest()
            ->take(6)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentPayouts', 'recentUsers', 'recentVideos', 'earningsChart'));
    }

    /**
     * Generate random video links for sharing
     */
    public function generateVideoLinks()
    {
        // Get all ready video IDs
        $allVideoIds = Video::where('status', 'ready')
            ->where('is_public', true)
            ->pluck('id')
            ->toArray();

        // Get already shared video IDs from session
        $sharedVideoIds = session('shared_video_ids', []);

        // Get videos that haven't been shared yet
        $availableVideoIds = array_diff($allVideoIds, $sharedVideoIds);

        // If all videos have been shared, reset
        if (empty($availableVideoIds)) {
            $sharedVideoIds = [];
            $availableVideoIds = $allVideoIds;
            session(['shared_video_ids' => []]);
        }

        // Randomly pick up to 5 videos
        $pickCount = min(5, count($availableVideoIds));
        $randomKeys = array_rand(array_flip($availableVideoIds), $pickCount);
        
        // Ensure it's always an array
        if (!is_array($randomKeys)) {
            $randomKeys = [$randomKeys];
        }

        // Get the videos
        $videos = Video::whereIn('id', $randomKeys)->get();

        // Update session with newly shared videos
        $newSharedIds = array_merge($sharedVideoIds, $randomKeys);
        session(['shared_video_ids' => $newSharedIds]);

        // Generate formatted links
        $baseUrl = config('app.url');
        $links = [];
        $i = 1;
        foreach ($videos as $video) {
            $links[] = $i . '. ' . $baseUrl . '/v/' . $video->slug;
            $i++;
        }

        $linksText = implode("\n", $links);
        $remainingCount = count($allVideoIds) - count($newSharedIds);
        $totalCount = count($allVideoIds);

        return view('admin.link-generator', compact('linksText', 'remainingCount', 'totalCount', 'videos'));
    }

    /**
     * Reset shared video links session
     */
    public function resetSharedLinks()
    {
        session()->forget('shared_video_ids');
        return redirect()->route('admin.generate-links')->with('success', 'Link sharing history telah direset.');
    }
}
