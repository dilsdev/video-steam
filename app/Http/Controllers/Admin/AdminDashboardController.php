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
}
