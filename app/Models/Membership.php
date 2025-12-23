<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membership extends Model
{
    protected $fillable = [
        'user_id',
        'plan',
        'original_price',
        'discount',
        'final_price',
        'payment_method',
        'payment_reference',
        'starts_at',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'discount' => 'decimal:2',
            'final_price' => 'decimal:2',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voucherUsage()
    {
        return $this->hasOne(VoucherUsage::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }
}
