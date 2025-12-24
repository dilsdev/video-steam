<?php

namespace App\Services;

use App\Models\User;
use App\Models\Membership;
use Illuminate\Support\Facades\DB;

class MembershipService
{
    public function getPlans(): array
    {
        return [
            'monthly' => [
                'price' => 20000, 
                'duration' => 30, 
                'label' => 'Bulanan',
                'payment_url' => config('services.lynk.payment_url_monthly', '#')
            ],
            'yearly' => [
                'price' => 199000, 
                'duration' => 365, 
                'label' => 'Tahunan',
                'payment_url' => config('services.lynk.payment_url_yearly', '#')
            ],
        ];
    }

    public function getPlanPrice(string $plan): int
    {
        $plans = $this->getPlans();
        return $plans[$plan]['price'] ?? 0;
    }

    public function getPlanDuration(string $plan): int
    {
        $plans = $this->getPlans();
        return $plans[$plan]['duration'] ?? 0;
    }

    public function getPlanLabel(string $plan): string
    {
        $plans = $this->getPlans();
        return $plans[$plan]['label'] ?? ucfirst($plan);
    }

    /**
     * Activate membership from webhook payment
     * Creates user if not exists, or extends existing membership
     */
    public function activateMembershipFromWebhook(
        string $email,
        ?string $name,
        ?string $phone,
        int $months,
        ?string $paymentReference
    ): Membership
    {
        return DB::transaction(function() use ($email, $name, $phone, $months, $paymentReference) {
            // Find or create user by email
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => $name ?? explode('@', $email)[0],
                    'email' => $email,
                    'password' => bcrypt(str()->random(16)), // Random password
                    'role' => 'viewer',
                    'is_verified' => true, // Auto-verify since they paid
                ]);
            }

            // Calculate duration in days (1 month = 30 days)
            $durationDays = $months * 30;
            
            // Calculate price based on months
            $plans = $this->getPlans();
            $pricePerMonth = $plans['monthly']['price'];
            $originalPrice = $pricePerMonth * $months;

            // Determine start date - extend if already member, otherwise start now
            $now = now();
            if ($user->hasActiveMembership() && $user->member_until) {
                $startsAt = $user->member_until;
            } else {
                $startsAt = $now;
            }
            
            // Calculate expiry date
            $expiresAt = $startsAt->copy()->addDays($durationDays);
            
            $membership = Membership::create([
                'user_id' => $user->id,
                'plan' => $months == 12 ? 'yearly' : ($months == 1 ? 'monthly' : 'custom'),
                'original_price' => $originalPrice,
                'discount' => 0,
                'final_price' => $originalPrice,
                'payment_method' => 'lynk',
                'payment_reference' => $paymentReference,
                'starts_at' => $startsAt,
                'expires_at' => $expiresAt,
                'status' => 'active'
            ]);
            
            // Update user membership status
            $user->update([
                'is_member' => true,
                'member_until' => $expiresAt
            ]);
            
            return $membership;
        });
    }
}
