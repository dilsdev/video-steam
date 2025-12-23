<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdConfig extends Model
{
    protected $fillable = [
        'name',
        'type',
        'provider',
        'script',
        'duration',
        'cpm_rate',
        'priority',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'cpm_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public static function getActiveAds()
    {
        return static::where('is_active', true)
            ->orderBy('priority', 'desc')
            ->get();
    }

    public static function getAverageCpm(): float
    {
        return static::where('is_active', true)->avg('cpm_rate') ?? 2.00;
    }
}
