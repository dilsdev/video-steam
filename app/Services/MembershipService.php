<?php

namespace App\Services;

use App\Models\User;
use App\Models\Membership;
use App\Models\Voucher;
use App\Models\VoucherUsage;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    private array $plans = [
        'monthly' => ['price' => 50000, 'duration' => 30, 'label' => 'Bulanan'],
        'yearly' => ['price' => 500000, 'duration' => 365, 'label' => 'Tahunan'],
    ];

    public function getPlans(): array
    {
        return $this->plans;
    }

    public function getPlanPrice(string $plan): int
    {
        return $this->plans[$plan]['price'] ?? 0;
    }

    public function getPlanDuration(string $plan): int
    {
        return $this->plans[$plan]['duration'] ?? 0;
    }

    public function getPlanLabel(string $plan): string
    {
        return $this->plans[$plan]['label'] ?? ucfirst($plan);
    }

    /**
     * Validasi voucher
     */
    public function validateVoucher(string $code, User $user, float $amount): ?array
    {
        $voucher = Voucher::where('code', $code)->first();
        
        if (!$voucher || !$voucher->canBeUsed()) {
            return null;
        }
        
        // Cek apakah user sudah pernah pakai voucher ini
        $usedByUser = VoucherUsage::where('voucher_id', $voucher->id)
            ->where('user_id', $user->id)
            ->count();
        
        if ($usedByUser >= $voucher->max_uses_per_user) {
            return null;
        }
        
        // Cek minimum purchase
        if ($amount < $voucher->min_purchase) {
            return null;
        }
        
        $discount = $voucher->calculateDiscount($amount);
        
        return [
            'voucher' => $voucher,
            'discount' => $discount,
            'final_price' => max(0, $amount - $discount)
        ];
    }

    /**
     * Aktivasi membership
     */
    public function activateMembership(User $user, string $plan, ?string $voucherCode = null): Membership
    {
        $originalPrice = $this->getPlanPrice($plan);
        $duration = $this->getPlanDuration($plan);
        $discount = 0;
        $voucher = null;
        
        if ($voucherCode) {
            $voucherData = $this->validateVoucher($voucherCode, $user, $originalPrice);
            if ($voucherData) {
                $voucher = $voucherData['voucher'];
                $discount = $voucherData['discount'];
            }
        }
        
        $finalPrice = $originalPrice - $discount;
        
        return DB::transaction(function() use ($user, $plan, $originalPrice, $discount, $finalPrice, $duration, $voucher) {
            // Extend jika sudah member
            $startsAt = $user->hasActiveMembership() 
                ? $user->member_until 
                : now();
            
            $expiresAt = $startsAt->copy()->addDays($duration);
            
            $membership = Membership::create([
                'user_id' => $user->id,
                'plan' => $plan,
                'original_price' => $originalPrice,
                'discount' => $discount,
                'final_price' => $finalPrice,
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'status' => 'active'
            ]);
            
            // Update user
            $user->update([
                'is_member' => true,
                'member_until' => $expiresAt
            ]);
            
            // Record voucher usage
            if ($voucher) {
                $voucher->increment('used_count');
                VoucherUsage::create([
                    'voucher_id' => $voucher->id,
                    'user_id' => $user->id,
                    'membership_id' => $membership->id,
                    'discount_amount' => $discount,
                    'used_at' => now()
                ]);
            }
            
            return $membership;
        });
    }
}
