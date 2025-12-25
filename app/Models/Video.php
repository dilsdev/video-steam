<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Video extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'slug',
        'title',
        'description',
        'filename',
        'external_url',
        'original_name',
        'thumbnail',
        'preview_filename',
        'mime_type',
        'file_size',
        'duration',
        'status',
        'total_views',
        'total_earnings',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'total_earnings' => 'decimal:2',
            'is_public' => 'boolean',
            'file_size' => 'integer',
            'duration' => 'integer',
            'total_views' => 'integer',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($video) {
            if (empty($video->slug)) {
                do {
                    $slug = Str::lower(Str::random(8));
                } while (static::where('slug', $slug)->exists());
                $video->slug = $slug;
            }
        });
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function views()
    {
        return $this->hasMany(VideoView::class);
    }

    public function earnings()
    {
        return $this->hasMany(Earning::class);
    }

    public function tokens()
    {
        return $this->hasMany(VideoToken::class);
    }

    // Route Key
    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Helpers
    public function getStoragePath(): string
    {
        // File disimpan via storeAs('private/videos', ...) yang relatif ke storage/app
        return storage_path('app/private/private/videos/'.$this->filename);
    }

    public function getThumbnailUrl(): string
    {
        if ($this->thumbnail && Storage::disk('public')->exists('thumbnails/'.$this->thumbnail)) {
            return asset('storage/thumbnails/'.$this->thumbnail);
        }

        return asset('images/default-thumbnail.svg');
    }

    public function getWatchUrl(): string
    {
        return route('videos.show', $this->slug);
    }

    /**
     * Get direct video URL (external or local stream)
     * Returns external_url if set, otherwise null (use token-based streaming)
     */
    public function getDirectVideoUrl(): ?string
    {
        return $this->external_url ?: null;
    }

    /**
     * Check if video uses external URL
     */
    public function hasExternalUrl(): bool
    {
        return ! empty($this->external_url);
    }

    public function isReady(): bool
    {
        // Untuk development, hanya cek status
        // File check dilakukan saat streaming saja
        return $this->status === 'ready';
    }

    public function hasFile(): bool
    {
        return file_exists($this->getStoragePath());
    }

    /**
     * Get path to preview file
     */
    public function getPreviewPath(): string
    {
        if ($this->preview_filename) {
            return storage_path('app/private/previews/'.$this->preview_filename);
        }

        return '';
    }

    /**
     * Check if video has preview
     */
    public function hasPreview(): bool
    {
        if (! $this->preview_filename) {
            return false;
        }

        return file_exists($this->getPreviewPath());
    }

    public function getFormattedSize(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' bytes';
    }

    public function getFormattedDuration(): string
    {
        $seconds = $this->duration;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%d:%02d', $minutes, $secs);
    }
}
