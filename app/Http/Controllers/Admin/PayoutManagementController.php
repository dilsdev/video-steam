<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PayoutManagementController extends Controller
{
    /**
     * List all payouts
     */
    public function index(Request $request)
    {
        $query = Payout::with('user:id,name,email');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $payouts = $query->latest()->paginate(20);
        
        $statusCounts = [
            'pending' => Payout::where('status', 'pending')->count(),
            'processing' => Payout::where('status', 'processing')->count(),
            'completed' => Payout::where('status', 'completed')->count(),
            'failed' => Payout::where('status', 'failed')->count(),
        ];

        return view('admin.payouts.index', compact('payouts', 'statusCounts'));
    }

    /**
     * Show payout detail
     */
    public function show(Payout $payout)
    {
        $payout->load(['user', 'processor']);
        return view('admin.payouts.show', compact('payout'));
    }

    /**
     * Process payout
     */
    public function process(Request $request, Payout $payout)
    {
        $request->validate([
            'status' => 'required|in:processing,completed,failed,cancelled',
            'admin_notes' => 'nullable|string|max:500',
            'proof_file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048'
        ]);

        $data = [
            'status' => $request->status,
            'admin_notes' => $request->admin_notes,
            'processed_by' => auth()->id(),
            'processed_at' => now()
        ];

        if ($request->hasFile('proof_file')) {
            $file = $request->file('proof_file');
            $filename = 'payout-' . $payout->id . '-' . time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('private/payouts', $filename);
            $data['proof_file'] = $filename;
        }

        // Jika cancelled atau failed, kembalikan balance
        if (in_array($request->status, ['cancelled', 'failed']) && $payout->status === 'pending') {
            $payout->user->increment('balance', $payout->amount);
        }

        $payout->update($data);

        return redirect()->route('admin.payouts.index')
            ->with('success', 'Payout berhasil diproses!');
    }
}
