<?php
/**
 * ARSITEKTUR VIDEO PLATFORM - BAGIAN 4: BLADE VIEWS & FRONTEND
 */

// ============================================================================
// BAGIAN 15: BLADE VIEWS STRUCTURE
// ============================================================================

/**
 * resources/views/
 * ├── layouts/
 * │   └── app.blade.php
 * ├── components/
 * │   ├── video-card.blade.php
 * │   └── ad-player.blade.php
 * ├── videos/
 * │   ├── index.blade.php
 * │   ├── show.blade.php
 * │   └── create.blade.php
 * ├── dashboard.blade.php
 * ├── memberships/
 * │   └── index.blade.php
 * ├── payouts/
 * │   └── index.blade.php
 * └── admin/
 *     ├── dashboard.blade.php
 *     └── payouts/
 *         └── index.blade.php
 */

// ============================================================================
// resources/views/videos/show.blade.php
// URL: domain.com/v/a8a6sha
// ============================================================================
?>

@extends('layouts.app')

@section('title', $video->title)

@section('content')
<div class="video-container">
    <div class="video-wrapper">
        <!-- Ad Overlay (untuk non-member) -->
        @if(!$skipAds && count($ads) > 0)
        <div id="ad-overlay" class="ad-overlay">
            <div class="ad-content">
                <p>Iklan akan selesai dalam <span id="ad-countdown">5</span> detik</p>
                <div id="ad-container">
                    {!! $ads->first()->script !!}
                </div>
            </div>
        </div>
        @endif

        <!-- Video Player -->
        <video id="video-player" 
               controls 
               poster="{{ $video->getThumbnailUrl() }}"
               @if(!$skipAds) style="display: none;" @endif>
            <source src="" type="video/mp4">
            Browser tidak support video.
        </video>
    </div>

    <div class="video-info">
        <h1>{{ $video->title }}</h1>
        <div class="video-meta">
            <span>{{ number_format($video->total_views) }} views</span>
            <span>By: {{ $video->user->name }}</span>
        </div>
        <div class="video-description">
            {!! nl2br(e($video->description)) !!}
        </div>
    </div>

    @if(!$skipAds)
    <div class="membership-promo">
        <p>Tidak ingin melihat iklan? <a href="{{ route('memberships.index') }}">Jadi Member</a></p>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('video-player');
    const adOverlay = document.getElementById('ad-overlay');
    const adCountdown = document.getElementById('ad-countdown');
    const skipAds = {{ $skipAds ? 'true' : 'false' }};

    // Ambil token streaming
    async function getStreamToken() {
        try {
            const response = await fetch('{{ route("videos.token", $video) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error getting stream token:', error);
            return null;
        }
    }

    // Konfirmasi iklan sudah ditonton
    async function confirmAdWatched(token) {
        try {
            await fetch(`/stream/${token}/ad-watched`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
        } catch (error) {
            console.error('Error confirming ad:', error);
        }
    }

    // Inisialisasi player
    async function initPlayer() {
        const tokenData = await getStreamToken();
        if (!tokenData || !tokenData.token) {
            alert('Gagal memuat video');
            return;
        }

        video.querySelector('source').src = tokenData.stream_url;
        video.load();

        if (skipAds) {
            video.style.display = 'block';
        } else {
            // Tampilkan iklan dulu
            let countdown = 5;
            const timer = setInterval(function() {
                countdown--;
                adCountdown.textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(timer);
                    adOverlay.style.display = 'none';
                    video.style.display = 'block';
                    confirmAdWatched(tokenData.token);
                }
            }, 1000);
        }
    }

    initPlayer();
});
</script>
@endpush

@push('styles')
<style>
.video-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.video-wrapper {
    position: relative;
    background: #000;
    border-radius: 8px;
    overflow: hidden;
}

#video-player {
    width: 100%;
    max-height: 70vh;
}

.ad-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.ad-content {
    text-align: center;
    color: #fff;
}

.ad-content p {
    margin-bottom: 20px;
    font-size: 14px;
}

#ad-countdown {
    font-weight: bold;
    font-size: 18px;
}

.video-info {
    padding: 20px 0;
}

.video-info h1 {
    font-size: 24px;
    margin-bottom: 10px;
}

.video-meta {
    color: #666;
    font-size: 14px;
    margin-bottom: 15px;
}

.video-meta span {
    margin-right: 15px;
}

.video-description {
    line-height: 1.6;
}

.membership-promo {
    background: #f0f7ff;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
    margin-top: 20px;
}

.membership-promo a {
    color: #0066cc;
    font-weight: bold;
}
</style>
@endpush
@endsection

<?php
// ============================================================================
// resources/views/videos/create.blade.php
// ============================================================================
?>

@extends('layouts.app')

@section('title', 'Upload Video')

@section('content')
<div class="upload-container">
    <h1>Upload Video Baru</h1>

    <form action="{{ route('uploader.videos.store') }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="upload-form">
        @csrf

        <div class="form-group">
            <label for="title">Judul Video *</label>
            <input type="text" 
                   id="title" 
                   name="title" 
                   value="{{ old('title') }}"
                   required
                   maxlength="255">
            @error('title')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="description">Deskripsi</label>
            <textarea id="description" 
                      name="description" 
                      rows="4">{{ old('description') }}</textarea>
            @error('description')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="video">File Video * (Max 500MB)</label>
            <input type="file" 
                   id="video" 
                   name="video" 
                   accept="video/mp4,video/mov,video/avi,video/webm"
                   required>
            @error('video')
                <span class="error">{{ $message }}</span>
            @enderror
            <div class="upload-progress" style="display: none;">
                <div class="progress-bar"></div>
                <span class="progress-text">0%</span>
            </div>
        </div>

        <div class="form-group">
            <label for="thumbnail">Thumbnail (Opsional)</label>
            <input type="file" 
                   id="thumbnail" 
                   name="thumbnail" 
                   accept="image/jpeg,image/png,image/webp">
            @error('thumbnail')
                <span class="error">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group checkbox">
            <label>
                <input type="checkbox" name="is_public" value="1" checked>
                Video ini publik (dapat dilihat semua orang)
            </label>
        </div>

        <button type="submit" class="btn-upload">Upload Video</button>
    </form>
</div>
@endsection

<?php
// ============================================================================
// resources/views/dashboard.blade.php
// ============================================================================
?>

@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard">
    <h1>Dashboard Uploader</h1>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Video</h3>
            <p class="stat-value">{{ $stats['total_videos'] }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Views</h3>
            <p class="stat-value">{{ number_format($stats['total_views']) }}</p>
        </div>
        <div class="stat-card">
            <h3>Total Penghasilan</h3>
            <p class="stat-value">Rp {{ number_format($stats['total_earnings']) }}</p>
        </div>
        <div class="stat-card highlight">
            <h3>Saldo Tersedia</h3>
            <p class="stat-value">Rp {{ number_format($stats['balance']) }}</p>
            @if($stats['balance'] >= $minPayout ?? 100000)
            <a href="{{ route('uploader.payouts.index') }}" class="btn-sm">Tarik Dana</a>
            @endif
        </div>
    </div>

    <!-- Recent Earnings -->
    <div class="section">
        <h2>Penghasilan Terbaru</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Video</th>
                    <th>Views</th>
                    <th>Penghasilan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentEarnings as $earning)
                <tr>
                    <td>{{ $earning->calculation_date->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('videos.show', $earning->video->slug) }}">
                            {{ Str::limit($earning->video->title, 40) }}
                        </a>
                    </td>
                    <td>{{ number_format($earning->views_count) }}</td>
                    <td>Rp {{ number_format($earning->amount) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">Belum ada penghasilan</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- My Videos -->
    <div class="section">
        <div class="section-header">
            <h2>Video Saya</h2>
            <a href="{{ route('uploader.videos.create') }}" class="btn-primary">Upload Video</a>
        </div>
        <div class="video-grid">
            @foreach($videos as $video)
            <div class="video-card">
                <img src="{{ $video->getThumbnailUrl() }}" alt="{{ $video->title }}">
                <div class="video-card-body">
                    <h4>{{ Str::limit($video->title, 50) }}</h4>
                    <p>{{ number_format($video->total_views) }} views</p>
                    <p>Rp {{ number_format($video->total_earnings) }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

<?php
// ============================================================================
// resources/views/memberships/index.blade.php
// ============================================================================
?>

@extends('layouts.app')

@section('title', 'Membership')

@section('content')
<div class="membership-page">
    <h1>Pilih Paket Membership</h1>
    <p class="subtitle">Nikmati video tanpa iklan!</p>

    @if($currentMembership)
    <div class="current-membership">
        <p>Membership Anda aktif hingga: <strong>{{ $currentMembership->expires_at->format('d M Y') }}</strong></p>
    </div>
    @endif

    <div class="plans-grid">
        @foreach($plans as $key => $plan)
        <div class="plan-card {{ $key === 'yearly' ? 'featured' : '' }}">
            @if($key === 'yearly')
            <div class="badge">Hemat 17%</div>
            @endif
            <h3>{{ ucfirst($key) }}</h3>
            <p class="price">Rp {{ number_format($plan['price']) }}</p>
            <p class="duration">{{ $plan['duration'] }} hari</p>
            <ul class="features">
                <li>✓ Tanpa iklan</li>
                <li>✓ Kualitas HD</li>
                <li>✓ Support uploader favorit</li>
            </ul>
            <form action="{{ route('memberships.store') }}" method="POST">
                @csrf
                <input type="hidden" name="plan" value="{{ $key }}">
                <div class="voucher-input">
                    <input type="text" 
                           name="voucher_code" 
                           placeholder="Kode voucher (opsional)"
                           class="voucher-field"
                           data-plan="{{ $key }}">
                    <button type="button" class="btn-check-voucher">Cek</button>
                </div>
                <div class="voucher-result" style="display: none;"></div>
                <button type="submit" class="btn-subscribe">Berlangganan</button>
            </form>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.btn-check-voucher').forEach(btn => {
    btn.addEventListener('click', async function() {
        const card = this.closest('.plan-card');
        const input = card.querySelector('.voucher-field');
        const result = card.querySelector('.voucher-result');
        const plan = input.dataset.plan;

        const response = await fetch('{{ route("memberships.validate-voucher") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ code: input.value, plan: plan })
        });

        const data = await response.json();
        result.style.display = 'block';

        if (data.valid) {
            result.innerHTML = `<span class="success">Diskon: Rp ${data.discount.toLocaleString()}<br>Total: Rp ${data.final_price.toLocaleString()}</span>`;
        } else {
            result.innerHTML = `<span class="error">${data.message || 'Voucher tidak valid'}</span>`;
        }
    });
});
</script>
@endpush
@endsection

<?php
// ============================================================================
// BAGIAN 16: POLICIES (AUTHORIZATION)
// ============================================================================

// app/Policies/VideoPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Video;

class VideoPolicy
{
    public function create(User $user): bool
    {
        return $user->isUploader();
    }

    public function update(User $user, Video $video): bool
    {
        return $user->id === $video->user_id || $user->isAdmin();
    }

    public function delete(User $user, Video $video): bool
    {
        return $user->id === $video->user_id || $user->isAdmin();
    }
}

// app/Providers/AppServiceProvider.php
use App\Models\Video;
use App\Policies\VideoPolicy;
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::policy(Video::class, VideoPolicy::class);
}

// ============================================================================
// BAGIAN 17: SEEDER DEFAULT DATA
// ============================================================================

// database/seeders/SettingsSeeder.php
namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'min_payout', 'value' => '100000', 'type' => 'int', 'group' => 'payout'],
            ['key' => 'payout_fee', 'value' => '2500', 'type' => 'int', 'group' => 'payout'],
            ['key' => 'default_cpm_rate', 'value' => '2.00', 'type' => 'string', 'group' => 'earning'],
            ['key' => 'membership_monthly_price', 'value' => '50000', 'type' => 'int', 'group' => 'membership'],
            ['key' => 'membership_yearly_price', 'value' => '500000', 'type' => 'int', 'group' => 'membership'],
            ['key' => 'max_video_size_mb', 'value' => '500', 'type' => 'int', 'group' => 'upload'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}

// database/seeders/AdConfigSeeder.php
namespace Database\Seeders;

use App\Models\AdConfig;
use Illuminate\Database\Seeder;

class AdConfigSeeder extends Seeder
{
    public function run(): void
    {
        AdConfig::create([
            'name' => 'Pre-roll Ad',
            'type' => 'preroll',
            'provider' => 'google_adsense',
            'script' => '<ins class="adsbygoogle" ...></ins><script>...</script>',
            'duration' => 5,
            'cpm_rate' => 2.00,
            'priority' => 10,
            'is_active' => true
        ]);
    }
}
