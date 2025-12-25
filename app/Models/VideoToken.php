<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoToken extends Model
{
    protected $guarded = [];

    public $incrementing = false;

    protected $primaryKey = 'token';

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'token',
        'video_id',
        'user_id',
        'ip_address',
        'session_id',
        'ad_watched',
        'expires_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'ad_watched' => 'boolean',
            'expires_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function video()
    {
        return $this->belongsTo(Video::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isValid(?string $ip = null, ?string $sessionId = null): bool
    {
        // Check if token has expired
        if (!$this->expires_at->isFuture()) {
            return false;
        }

        // Log IP mismatch for security monitoring (non-blocking)
        if ($ip && $this->ip_address && $this->ip_address !== $ip) {
            \Illuminate\Support\Facades\Log::warning('VideoToken IP mismatch', [
                'token' => substr($this->token, 0, 8) . '...',
                'video_id' => $this->video_id,
                'expected_ip' => $this->ip_address,
                'actual_ip' => $ip,
            ]);
        }

        return true;
    }

    public function markAdWatched(): void
    {
        $this->update(['ad_watched' => true]);
    }
}
