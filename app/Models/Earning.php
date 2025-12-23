<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    protected $fillable = [
        'user_id',
        'video_id',
        'views_count',
        'cpm_rate',
        'amount',
        'calculation_date',
    ];

    protected function casts(): array
    {
        return [
            'cpm_rate' => 'decimal:2',
            'amount' => 'decimal:2',
            'calculation_date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }
}
