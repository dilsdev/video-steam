<?php

namespace App\Http\Controllers;

use App\Services\MembershipService;

class MembershipController extends Controller
{
    public function __construct(
        private MembershipService $membershipService
    ) {}

    /**
     * Tampilkan paket membership (accessible to guests)
     */
    public function index()
    {
        $plans = $this->membershipService->getPlans();
        $currentMembership = null;

        // Only check membership if user is logged in
        if (auth()->check()) {
            $currentMembership = auth()->user()->memberships()
                ->where('status', 'active')
                ->where('expires_at', '>', now())
                ->latest()
                ->first();
        }

        return view('memberships.index', compact('plans', 'currentMembership'));
    }
}
