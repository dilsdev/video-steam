<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoToken extends Model
{
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

    public function isValid(string $ip, string $sessionId): bool
    {
        return $this->expires_at->isFuture() &&
               $this->ip_address === $ip &&
               $this->session_id === $sessionId;
    }

    public function markAdWatched(): void
    {
        $this->update(['ad_watched' => true]);
    }
}
