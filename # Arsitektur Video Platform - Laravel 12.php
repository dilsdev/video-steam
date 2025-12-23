<?php

/**
 * ============================================================================
 * ARSITEKTUR VIDEO PLATFORM - LARAVEL 12 + MYSQL 8.4
 * SHARED HOSTING (TANPA ROOT ACCESS)
 * ============================================================================
 *
 * URL Format: domain.com/v/a8a6sha
 *
 * BATASAN SHARED HOSTING:
 * - Tidak ada FFmpeg (video tidak ditranskode)
 * - Tidak ada Redis/Supervisor (pakai database queue)
 * - Tidak ada X-Accel-Redirect (streaming via PHP)
 * - Cron via cPanel
 * - Video di luar public folder
 *
 * FITUR UTAMA:
 * 1. Upload video
 * 2. Penghasilan dari iklan
 * 3. Membership (skip iklan)
 * 4. Voucher untuk membership
 * 5. Dashboard uploader
 * 6. Transfer penghasilan
 * 7. Keamanan anti-hacker
 * 8. Video tidak bisa diakses langsung
 */

// ============================================================================
// BAGIAN 1: STRUKTUR FOLDER
// ============================================================================

/**
 * /video-platform
 * ├── app/
 * │   ├── Http/
 * │   │   ├── Controllers/
 * │   │   │   ├── Auth/
 * │   │   │   ├── Admin/
 * │   │   │   │   ├── AdminDashboardController.php
 * │   │   │   │   ├── PayoutManagementController.php
 * │   │   │   │   ├── VoucherManagementController.php
 * │   │   │   │   └── AdConfigController.php
 * │   │   │   ├── VideoController.php
 * │   │   │   ├── StreamController.php
 * │   │   │   ├── MembershipController.php
 * │   │   │   ├── DashboardController.php
 * │   │   │   └── PayoutController.php
 * │   │   ├── Middleware/
 * │   │   │   ├── CheckMembership.php
 * │   │   │   ├── CheckUploader.php
 * │   │   │   ├── CheckAdmin.php
 * │   │   │   ├── RateLimitStreaming.php
 * │   │   │   └── ValidateVideoToken.php
 * │   │   └── Requests/
 * │   │       ├── StoreVideoRequest.php
 * │   │       ├── StoreMembershipRequest.php
 * │   │       └── StorePayoutRequest.php
 * │   ├── Models/
 * │   ├── Policies/
 * │   ├── Services/
 * │   │   ├── VideoService.php
 * │   │   ├── StreamingService.php
 * │   │   ├── EarningService.php
 * │   │   └── MembershipService.php
 * │   └── Console/Commands/
 * ├── storage/
 * │   └── app/
 * │       └── private/
 * │           └── videos/  <- VIDEO DISIMPAN DI SINI (AMAN)
 * └── public/
 *     └── storage/
 *         └── thumbnails/  <- THUMBNAIL (PUBLIC)
 */

// ============================================================================
// BAGIAN 2: DATABASE MIGRATIONS
// ============================================================================

// 2024_01_01_000001_create_users_table.php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->enum('role', ['uploader', 'viewer', 'admin'])->default('viewer')->index();
    $table->string('payment_email')->nullable();
    $table->string('payment_method')->nullable(); // bank_transfer, ewallet
    $table->string('payment_account')->nullable(); // no rekening/ewallet
    $table->decimal('balance', 15, 2)->default(0);
    $table->boolean('is_member')->default(false);
    $table->timestamp('member_until')->nullable();
    $table->boolean('is_verified')->default(false);
    $table->string('verification_token')->nullable();
    $table->timestamp('email_verified_at')->nullable();
    $table->rememberToken();
    $table->timestamps();
    $table->index(['role', 'is_member']);
});

// 2024_01_01_000002_create_videos_table.php
Schema::create('videos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('slug', 10)->unique()->index();
    $table->string('title');
    $table->text('description')->nullable();
    $table->string('filename'); // nama file di storage/app/private/videos
    $table->string('original_name')->nullable();
    $table->string('thumbnail')->nullable();
    $table->string('mime_type')->default('video/mp4');
    $table->bigInteger('file_size')->default(0);
    $table->integer('duration')->default(0); // dalam detik
    $table->enum('status', ['processing', 'ready', 'failed', 'suspended'])->default('ready');
    $table->bigInteger('total_views')->default(0);
    $table->decimal('total_earnings', 15, 2)->default(0);
    $table->boolean('is_public')->default(true);
    $table->timestamps();
    $table->softDeletes();
    $table->index(['user_id', 'status', 'is_public']);
});

// 2024_01_01_000003_create_video_views_table.php
Schema::create('video_views', function (Blueprint $table) {
    $table->id();
    $table->foreignId('video_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
    $table->string('ip_address', 45);
    $table->string('session_id', 100);
    $table->string('user_agent')->nullable();
    $table->string('country', 5)->nullable();
    $table->boolean('is_member_view')->default(false); // tidak dihitung untuk earning
    $table->boolean('is_counted')->default(false);
    $table->timestamp('created_at');
    $table->index(['video_id', 'is_counted', 'is_member_view']);
    $table->index(['ip_address', 'session_id', 'created_at']);
});

// 2024_01_01_000004_create_memberships_table.php
Schema::create('memberships', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('plan', ['monthly', 'yearly']);
    $table->decimal('original_price', 10, 2);
    $table->decimal('discount', 10, 2)->default(0);
    $table->decimal('final_price', 10, 2);
    $table->string('payment_method')->nullable();
    $table->string('payment_reference')->nullable();
    $table->timestamp('starts_at');
    $table->timestamp('expires_at');
    $table->enum('status', ['pending', 'active', 'expired', 'cancelled'])->default('pending');
    $table->timestamps();
    $table->index(['user_id', 'status', 'expires_at']);
});

// 2024_01_01_000005_create_vouchers_table.php
Schema::create('vouchers', function (Blueprint $table) {
    $table->id();
    $table->string('code', 50)->unique();
    $table->string('name');
    $table->text('description')->nullable();
    $table->enum('type', ['percentage', 'fixed']);
    $table->decimal('value', 10, 2);
    $table->decimal('min_purchase', 10, 2)->default(0);
    $table->decimal('max_discount', 10, 2)->nullable();
    $table->integer('max_uses')->default(0); // 0 = unlimited
    $table->integer('max_uses_per_user')->default(1);
    $table->integer('used_count')->default(0);
    $table->timestamp('starts_at')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->index(['code', 'is_active', 'expires_at']);
});

// 2024_01_01_000006_create_voucher_usages_table.php
Schema::create('voucher_usages', function (Blueprint $table) {
    $table->id();
    $table->foreignId('voucher_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('membership_id')->constrained()->onDelete('cascade');
    $table->decimal('discount_amount', 10, 2);
    $table->timestamp('used_at');
    $table->unique(['voucher_id', 'user_id', 'membership_id']);
});

// 2024_01_01_000007_create_earnings_table.php
Schema::create('earnings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('video_id')->constrained()->onDelete('cascade');
    $table->integer('views_count');
    $table->decimal('cpm_rate', 10, 2);
    $table->decimal('amount', 15, 2);
    $table->date('calculation_date');
    $table->timestamps();
    $table->unique(['video_id', 'calculation_date']);
    $table->index(['user_id', 'calculation_date']);
});

// 2024_01_01_000008_create_payouts_table.php
Schema::create('payouts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->decimal('amount', 15, 2);
    $table->decimal('fee', 10, 2)->default(0);
    $table->decimal('net_amount', 15, 2);
    $table->string('payment_method');
    $table->string('payment_account');
    $table->string('payment_name');
    $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
    $table->text('notes')->nullable();
    $table->text('admin_notes')->nullable();
    $table->string('proof_file')->nullable();
    $table->timestamp('processed_at')->nullable();
    $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamps();
    $table->index(['user_id', 'status', 'created_at']);
});

// 2024_01_01_000009_create_ad_configs_table.php
Schema::create('ad_configs', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->enum('type', ['preroll', 'midroll', 'postroll', 'banner']);
    $table->enum('provider', ['google_adsense', 'custom', 'direct']);
    $table->text('script'); // kode iklan
    $table->integer('duration')->default(5); // durasi iklan dalam detik
    $table->decimal('cpm_rate', 10, 2)->default(2.00); // bayaran per 1000 views
    $table->integer('priority')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// 2024_01_01_000010_create_video_tokens_table.php
Schema::create('video_tokens', function (Blueprint $table) {
    $table->string('token', 64)->primary();
    $table->foreignId('video_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
    $table->string('ip_address', 45);
    $table->string('session_id', 100);
    $table->boolean('ad_watched')->default(false);
    $table->timestamp('expires_at');
    $table->timestamp('created_at');
    $table->index(['token', 'expires_at', 'ip_address']);
});

// 2024_01_01_000011_create_security_logs_table.php
Schema::create('security_logs', function (Blueprint $table) {
    $table->id();
    $table->string('event_type'); // login_failed, token_invalid, rate_limit, etc
    $table->string('ip_address', 45);
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
    $table->text('details')->nullable();
    $table->string('user_agent')->nullable();
    $table->timestamp('created_at');
    $table->index(['event_type', 'ip_address', 'created_at']);
});

// 2024_01_01_000012_create_settings_table.php
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value')->nullable();
    $table->string('type')->default('string'); // string, int, bool, json
    $table->string('group')->default('general');
    $table->timestamps();
});

// ============================================================================
// BAGIAN 3: MODELS
// ============================================================================

// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'payment_email', 'payment_method', 'payment_account',
        'balance', 'is_member', 'member_until',
        'is_verified', 'verification_token',
    ];

    protected $hidden = ['password', 'remember_token', 'verification_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'balance' => 'decimal:2',
            'is_member' => 'boolean',
            'is_verified' => 'boolean',
            'member_until' => 'datetime',
            'email_verified_at' => 'datetime',
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

    // Helpers
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

// app/Models/Video.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Video extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'slug', 'title', 'description', 'filename',
        'original_name', 'thumbnail', 'mime_type', 'file_size',
        'duration', 'status', 'total_views', 'total_earnings', 'is_public',
    ];

    protected function casts(): array
    {
        return [
            'total_earnings' => 'decimal:2',
            'is_public' => 'boolean',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($video) {
            if (empty($video->slug)) {
                do {
                    $slug = Str::random(8);
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

    // Helpers
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getStoragePath(): string
    {
        return storage_path('app/private/videos/'.$this->filename);
    }

    public function getThumbnailUrl(): string
    {
        return $this->thumbnail
            ? asset('storage/thumbnails/'.$this->thumbnail)
            : asset('images/default-thumbnail.jpg');
    }

    public function getWatchUrl(): string
    {
        return route('videos.show', $this->slug);
    }

    public function isReady(): bool
    {
        return $this->status === 'ready' && file_exists($this->getStoragePath());
    }
}

// app/Models/VideoToken.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoToken extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'token';

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'token', 'video_id', 'user_id', 'ip_address',
        'session_id', 'ad_watched', 'expires_at', 'created_at',
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

// app/Models/Setting.php (Helper untuk settings)

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group'];

    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            if (! $setting) {
                return $default;
            }

            return match ($setting->type) {
                'int' => (int) $setting->value,
                'bool' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'json' => json_decode($setting->value, true),
                default => $setting->value,
            };
        });
    }

    public static function set(string $key, $value, string $type = 'string'): void
    {
        if ($type === 'json') {
            $value = json_encode($value);
        }
        if ($type === 'bool') {
            $value = $value ? '1' : '0';
        }

        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value, 'type' => $type]
        );

        Cache::forget("setting.{$key}");
    }
}
