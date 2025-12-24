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
        $avg = static::where('is_active', true)->avg('cpm_rate');
        
        // If no active ads, use default from settings
        if ($avg === null) {
            return (float) Setting::get('default_cpm_rate', 1500);
        }
        
        return $avg;
    }
}
