@extends('layouts.app')

@section('title', $video->title)

@section('content')
    <div class="video-page" style="display: grid; grid-template-columns: 1fr 350px; gap: 2rem;">
        <div class="video-main">
            <div class="video-wrapper" style="position: relative; background: #000; border-radius: 16px; overflow: hidden;">
                <!-- Ad Overlay (untuk non-member) -->
                @if (!$skipAds && count($ads) > 0)
                    <div id="ad-overlay"
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.95); display: flex; align-items: center; justify-content: center; z-index: 10; flex-direction: column;">
                        <div style="text-align: center; color: white; padding: 2rem;">
                            <p style="margin-bottom: 1rem; font-size: 0.875rem; color: #94a3b8;">Iklan akan selesai dalam</p>
                            <div id="ad-countdown"
                                style="font-size: 3rem; font-weight: 700; color: #6366f1; margin-bottom: 1rem;">5</div>
                            <p style="font-size: 0.875rem; color: #64748b;">detik</p>
                            <div id="ad-container" style="margin-top: 2rem;">
                                {!! $ads->first()->script ??
                                    '<div style="background:#1e293b;padding:2rem;border-radius:8px;">Placeholder Iklan</div>' !!}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Video Player -->
                <video id="video-player" controls poster="{{ $video->getThumbnailUrl() }}"
                    style="width: 100%; max-height: 70vh; display: {{ $skipAds ? 'block' : 'none' }};">
                    <source src="" type="{{ $video->mime_type }}">
                    Browser tidak support video.
                </video>
            </div>

            <div class="video-info" style="padding: 1.5rem 0;">
                <h1 style="font-size: 1.5rem; margin-bottom: 0.5rem;">{{ $video->title }}</h1>
                <div style="color: #64748b; font-size: 0.875rem; margin-bottom: 1rem;">
                    <span>{{ number_format($video->total_views) }} views</span>
                    <span style="margin: 0 0.5rem;">•</span>
                    <span>{{ $video->created_at->diffForHumans() }}</span>
                </div>

                <div
                    style="display: flex; align-items: center; gap: 1rem; padding: 1rem 0; border-top: 1px solid rgba(255,255,255,0.1); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <div
                        style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #0ea5e9); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1.25rem;">
                        {{ substr($video->user->name, 0, 1) }}
                    </div>
                    <div>
                        <div style="font-weight: 600;">{{ $video->user->name }}</div>
                        <div style="font-size: 0.875rem; color: #64748b;">Uploader</div>
                    </div>
                </div>

                @if ($video->description)
                    <div style="margin-top: 1rem; color: #cbd5e1; line-height: 1.7;">
                        {!! nl2br(e($video->description)) !!}
                    </div>
                @endif
            </div>

            @if (!$skipAds)
                <div class="card"
                    style="background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(14,165,233,0.1)); text-align: center;">
                    <p style="margin-bottom: 1rem;">Tidak ingin melihat iklan?</p>
                    <a href="{{ route('memberships.index') }}" class="btn btn-primary">Jadi Member Sekarang</a>
                </div>
            @endif
        </div>

        <div class="video-sidebar">
            <h3 style="margin-bottom: 1rem; font-size: 1rem;">Video Lainnya</h3>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                @foreach ($relatedVideos as $related)
                    <a href="{{ route('videos.show', $related) }}"
                        style="display: flex; gap: 0.75rem; padding: 0.5rem; border-radius: 8px; transition: background 0.2s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.05)'"
                        onmouseout="this.style.background='transparent'">
                        <img src="{{ $related->getThumbnailUrl() }}" alt="{{ $related->title }}"
                            style="width: 120px; height: 68px; object-fit: cover; border-radius: 8px;">
                        <div>
                            <div
                                style="font-size: 0.875rem; font-weight: 500; color: white; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $related->title }}</div>
                            <div style="font-size: 0.75rem; color: #64748b; margin-top: 0.25rem;">
                                {{ number_format($related->total_views) }} views</div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            @media (max-width: 1024px) {
                .video-page {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const video = document.getElementById('video-player');
                const adOverlay = document.getElementById('ad-overlay');
                const adCountdown = document.getElementById('ad-countdown');
                const skipAds = {{ $skipAds ? 'true' : 'false' }};

                async function getStreamToken() {
                    try {
                        const response = await fetch('{{ route('videos.token', $video) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken
                            }
                        });
                        return await response.json();
                    } catch (error) {
                        console.error('Error getting stream token:', error);
                        return null;
                    }
                }

                async function confirmAdWatched(token) {
                    try {
                        await fetch(`/stream/${token}/ad-watched`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken
                            }
                        });
                    } catch (error) {
                        console.error('Error confirming ad:', error);
                    }
                }

                async function initPlayer() {
                    const tokenData = await getStreamToken();
                    if (!tokenData || !tokenData.token) {
                        alert('Gagal memuat video. Silakan refresh halaman.');
                        return;
                    }

                    video.querySelector('source').src = tokenData.stream_url;
                    video.load();

                    if (skipAds) {
                        video.style.display = 'block';
                    } else if (adOverlay) {
                        let countdown = 5;
                        const timer = setInterval(function() {
                            countdown--;
                            if (adCountdown) adCountdown.textContent = countdown;
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
@endsection
