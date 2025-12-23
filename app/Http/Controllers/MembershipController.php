<?php

namespace App\Http\Controllers;

use App\Services\MembershipService;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function __construct(
        private MembershipService $membershipService
    ) {}

    /**
     * Tampilkan paket membership
     */
    public function index()
    {
        $plans = $this->membershipService->getPlans();
        $currentMembership = auth()->user()->memberships()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        return view('memberships.index', compact('plans', 'currentMembership'));
    }

    /**
     * Aktivasi membership
     */
    public function store(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:monthly,yearly',
            'voucher_code' => 'nullable|string|max:50'
        ]);

        try {
            $membership = $this->membershipService->activateMembership(
                auth()->user(),
                $request->plan,
                $request->voucher_code
            );

            return redirect()->route('home')
                ->with('success', 'Membership berhasil diaktifkan hingga ' . $membership->expires_at->format('d M Y'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal mengaktifkan membership: ' . $e->getMessage()]);
        }
    }

    /**
     * Validasi voucher via AJAX
     */
    public function validateVoucher(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'plan' => 'required|in:monthly,yearly'
        ]);

        $amount = $this->membershipService->getPlanPrice($request->plan);
        $result = $this->membershipService->validateVoucher(
            $request->code,
            auth()->user(),
            $amount
        );

        if (!$result) {
            return response()->json([
                'valid' => false, 
                'message' => 'Voucher tidak valid atau sudah digunakan'
            ]);
        }

        return response()->json([
            'valid' => true,
            'discount' => $result['discount'],
            'final_price' => $result['final_price'],
            'voucher_type' => $result['voucher']->type,
            'voucher_value' => $result['voucher']->value
        ]);
    }
}
