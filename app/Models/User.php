<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'payment_email',
        'payment_method',
        'payment_account',
        'balance',
        'is_member',
        'member_until',
        'is_verified',
        'verification_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'is_member' => 'boolean',
            'is_verified' => 'boolean',
            'member_until' => 'datetime',
        ];
    }

    // Relationships
    public function videos()
    {
        return $this->hasMany(Video::class);
    }

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function earnings()
    {
        return $this->hasMany(Earning::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function videoViews()
    {
        return $this->hasMany(VideoView::class);
    }

    // Role Helpers
    public function isUploader(): bool
    {
        return $this->role === 'uploader';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }

    // Membership Helpers
    public function hasActiveMembership(): bool
    {
        return $this->is_member && 
               $this->member_until && 
               $this->member_until->isFuture();
    }

    public function canWithdraw(): bool
    {
        $minPayout = Setting::get('min_payout', 100000);
        return $this->balance >= $minPayout;
    }

    public function refreshMembershipStatus(): void
    {
        if ($this->member_until && $this->member_until->isPast()) {
            $this->update(['is_member' => false]);
        }
    }
}
