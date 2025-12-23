<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'event_type',
        'ip_address',
        'user_id',
        'details',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $eventType, ?string $ip = null, ?int $userId = null, ?string $details = null, ?string $userAgent = null): self
    {
        return static::create([
            'event_type' => $eventType,
            'ip_address' => $ip ?? request()->ip(),
            'user_id' => $userId ?? auth()->id(),
            'details' => $details,
            'user_agent' => $userAgent ?? request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
