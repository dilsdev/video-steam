<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'video_id',
        'user_id',
        'ip_address',
        'session_id',
        'user_agent',
        'country',
        'is_member_view',
        'is_counted',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_member_view' => 'boolean',
            'is_counted' => 'boolean',
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
}
