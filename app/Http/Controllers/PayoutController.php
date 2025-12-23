<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use App\Models\Setting;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    /**
     * Daftar payout user
     */
    public function index()
    {
        $payouts = auth()->user()->payouts()->latest()->paginate(20);
        $minPayout = Setting::get('min_payout', 100000);
        $payoutFee = Setting::get('payout_fee', 2500);
        $balance = auth()->user()->balance;

        return view('payouts.index', compact('payouts', 'minPayout', 'payoutFee', 'balance'));
    }

    /**
     * Form request payout
     */
    public function create()
    {
        $minPayout = Setting::get('min_payout', 100000);
        $payoutFee = Setting::get('payout_fee', 2500);
        $balance = auth()->user()->balance;

        if ($balance < $minPayout) {
            return redirect()->route('uploader.payouts.index')
                ->withErrors(['error' => 'Saldo tidak mencukupi untuk penarikan. Minimum: Rp ' . number_format($minPayout)]);
        }

        return view('payouts.create', compact('minPayout', 'payoutFee', 'balance'));
    }

    /**
     * Submit payout request
     */
    public function store(Request $request)
    {
        $minPayout = Setting::get('min_payout', 100000);
        $maxPayout = auth()->user()->balance;
        $payoutFee = Setting::get('payout_fee', 2500);

        $request->validate([
            'amount' => "required|numeric|min:{$minPayout}|max:{$maxPayout}",
            'payment_method' => 'required|in:bank_transfer,dana,gopay,ovo,shopeepay',
            'payment_account' => 'required|string|max:50',
            'payment_name' => 'required|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = auth()->user();
        $netAmount = $request->amount - $payoutFee;

        Payout::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'fee' => $payoutFee,
            'net_amount' => $netAmount,
            'payment_method' => $request->payment_method,
            'payment_account' => $request->payment_account,
            'payment_name' => $request->payment_name,
            'notes' => $request->notes,
            'status' => 'pending'
        ]);

        $user->decrement('balance', $request->amount);

        return redirect()->route('uploader.payouts.index')
            ->with('success', 'Permintaan penarikan berhasil diajukan!');
    }

    /**
     * Cancel payout request
     */
    public function cancel(Payout $payout)
    {
        if ($payout->user_id !== auth()->id()) {
            abort(403);
        }

        if ($payout->status !== 'pending') {
            return back()->withErrors(['error' => 'Hanya payout dengan status pending yang dapat dibatalkan']);
        }

        // Kembalikan balance
        auth()->user()->increment('balance', $payout->amount);
        
        $payout->update(['status' => 'cancelled']);

        return redirect()->route('uploader.payouts.index')
            ->with('success', 'Permintaan penarikan berhasil dibatalkan!');
    }
}
