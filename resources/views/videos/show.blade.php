@extends('layouts.app')

@section('title', $video->title)

@section('content')
    @push('styles')
        <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
        <style>
            /* Video page styles */
            .video-content-wrapper {
                display: block;
            }

            /* Video with sidebar ads layout */
            .video-with-sidebar-ads {
                display: flex;
                gap: 1rem;
                align-items: flex-start;
                justify-content: center;
            }

            .sidebar-ad {
                flex-shrink: 0;
                width: 160px;
                min-height: 600px;
                display: flex;
                flex-direction: column;
                gap: 1rem;
                background: rgba(30, 41, 59, 0.3);
                border-radius: 12px;
                padding: 0.5rem;
                align-items: center;
            }

            .video-center-content {
                flex: 1;
                max-width: 900px;
                min-width: 0;
            }

            /* Hide sidebar ads on smaller screens */
            @media (max-width: 1200px) {
                .sidebar-ad {
                    display: none;
                }
            }

            /* Bottom ads container */
            .bottom-ads-container {
                display: flex;
                flex-wrap: wrap;
                gap: 1.5rem;
                justify-content: center;
                margin: 1.5rem 0;
            }

            .bottom-ads-container>div {
                background: rgba(30, 41, 59, 0.3);
                border-radius: 12px;
                padding: 1rem;
                display: flex;
                justify-content: center;
                align-items: center;
            }

            @media (max-width: 768px) {
                .promo-modal-content {
                    padding: 1.5rem !important;
                    max-width: 95% !important;
                }

                .promo-modal-content h2 {
                    font-size: 1.5rem !important;
                }

                .promo-modal-content p {
                    font-size: 1rem !important;
                }

                .related-videos-grid {
                    grid-template-columns: 1fr !important;
                }

                .bottom-ads-container>div {
                    width: 100%;
                }
            }

            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }

            :root {
                --plyr-color-main: #6366f1;
                --plyr-video-background: #000;
                --plyr-menu-background: #1e293b;
                --plyr-menu-color: #fff;
            }

            .plyr {
                border-radius: 16px;
                overflow: hidden;
            }

            .plyr--video {
                background: #000;
            }

            .plyr__control--overlaid {
                background: linear-gradient(135deg, #6366f1, #0ea5e9) !important;
                border: none !important;
                box-shadow: 0 10px 40px rgba(99, 102, 241, 0.5);
            }

            .plyr__control--overlaid:hover {
                background: linear-gradient(135deg, #4f46e5, #0284c7) !important;
            }

            .plyr--full-ui input[type=range] {
                color: #6366f1;
            }

            .plyr__control:hover {
                background: rgba(99, 102, 241, 0.3);
            }

            .plyr__menu__container {
                background: #1e293b;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    @endpush

    {{-- Modal Promo (Logic Tetap) --}}
    @if (!$skipAds)
        <div id="promo-modal-1"
            style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
            <div class="promo-modal-content"
                style="position: relative; background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 20px; padding: 2rem 3rem; max-width: 600px; width: 95%; text-align: center; border: 1px solid rgba(99,102,241,0.3); box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
                <button class="close-modal" data-modal="promo-modal-1"
                    style="position: absolute; top: 0.75rem; right: 0.75rem; background: rgba(255,255,255,0.1); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1.5rem; line-height: 1;">×</button>
                <p style="font-size: 1.5rem; color: #e2e8f0; margin-bottom: 1rem; line-height: 1.5;">
                    <strong
                        style="background: linear-gradient(135deg, #10b981, #0ea5e9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 2rem;">Rp20.000</strong>
                    <span style="color: #94a3b8;">/ 1 Bulan</span>
                </p>
                <p style="font-size: 1.25rem; color: #fff; margin-bottom: 1.5rem;">Nonton Semua Video Tanpa Iklan</p>
                <a href="{{ route('memberships.index') }}"
                    style="display: inline-block; background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; padding: 1rem 3rem; border-radius: 12px; font-weight: 700; font-size: 1.1rem; text-decoration: none;">Daftar
                    Sekarang →</a>
                <p style="margin-top: 1rem; font-size: 0.85rem; color: #64748b; cursor: pointer;"
                    onclick="document.getElementById('promo-modal-1').style.display='none'">Nanti saja</p>
            </div>
        </div>

        <div id="promo-modal-2"
            style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.85); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
            <div class="promo-modal-content"
                style="position: relative; background: linear-gradient(135deg, #1e293b, #0f172a); border-radius: 20px; padding: 2rem 3rem; max-width: 650px; width: 95%; text-align: center; border: 1px solid rgba(99,102,241,0.3); box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
                <button class="close-modal" data-modal="promo-modal-2"
                    style="position: absolute; top: 0.75rem; right: 0.75rem; background: rgba(255,255,255,0.1); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; font-size: 1.5rem; line-height: 1;">×</button>
                <h2 style="font-size: 2rem; font-weight: 800; margin-bottom: 1rem; color: #f59e0b;">😤 Capek Iklan terus?
                </h2>
                <p style="font-size: 1.25rem; color: #e2e8f0; margin-bottom: 1.5rem; line-height: 1.6;">
                    <strong
                        style="background: linear-gradient(135deg, #10b981, #0ea5e9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-size: 1.75rem;">Rp20.000</strong>
                    <span style="color: #94a3b8;">=</span>
                    <strong style="color: #fff;">1 Bulan Nonton Tanpa Iklan</strong>
                </p>
                <a href="{{ route('memberships.index') }}"
                    style="display: inline-block; background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; padding: 1rem 3rem; border-radius: 12px; font-weight: 700; font-size: 1.1rem; text-decoration: none;">Daftar
                    Sekarang →</a>
                <p style="margin-top: 1rem; font-size: 0.85rem; color: #64748b; cursor: pointer;"
                    onclick="document.getElementById('promo-modal-2').style.display='none'">Lanjutkan dengan iklan</p>
            </div>
        </div>
    @endif

    <div class="video-page" style="display: flex; flex-direction: column; gap: 1.5rem;">

        {{-- Header Ad (728x90) - Di atas video --}}
        @if (!$skipAds)
            <div class="ad-header"
                style="display: flex; justify-content: center; background: rgba(30,41,59,0.3); border-radius: 12px; padding: 1rem; overflow: hidden;">
                <div style="max-width: 728px; width: 100%;">
                    <script async type="application/javascript" src="https://a.magsrv.com/ad-provider.js"></script>
                    <ins class="eas6a97888e2" data-zoneid="5812374"></ins>
                    <script>
                        (AdProvider = window.AdProvider || []).push({
                            "serve": {}
                        });
                    </script>
                </div>
            </div>
        @endif

        {{-- Video with Sidebar Ads Layout --}}
        <div class="video-with-sidebar-ads">
            {{-- Left Sidebar Ad (160x600) --}}
            @if (!$skipAds)
                <div class="sidebar-ad">
                    <script async type="application/javascript" src="https://a.magsrv.com/ad-provider.js"></script>
                    <ins class="eas6a97888e2" data-zoneid="5812376"></ins>
                    <script>
                        (AdProvider = window.AdProvider || []).push({
                            "serve": {}
                        });
                    </script>
                </div>
            @endif

            {{-- Center: Video Content --}}
            <div class="video-center-content">
                <div class="video-main">
                    {{-- Video Container --}}
                    <div id="video-container"
                        style="position: relative; width: 100%; background: #000; border-radius: 16px; overflow: hidden;">
                        <div id="video-loading"
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; min-height: 400px; display: flex; align-items: center; justify-content: center; background: #0f172a; z-index: 10;">
                            <div style="text-align: center;">
                                <div
                                    style="width: 50px; height: 50px; border: 3px solid rgba(99,102,241,0.3); border-top-color: #6366f1; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem;">
                                </div>
                                <p style="color: #64748b;">Memuat video...</p>
                            </div>
                        </div>
                        <video id="player" playsinline controls poster="{{ $video->getThumbnailUrl() }}">
                            <source src="" type="{{ $video->mime_type }}" />
                        </video>
                    </div>

                    {{-- Video Info --}}
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
                            <button id="share-btn" class="btn btn-secondary"
                                style="margin-left: auto; display: flex; align-items: center; gap: 0.5rem; background: var(--bg-hover); border: 1px solid var(--border);">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                </svg>
                                Copy Link Video
                            </button>
                        </div>
                        @if ($video->description)
                            <div style="margin-top: 1rem; color: #cbd5e1; line-height: 1.7;">
                                {!! nl2br(e($video->description)) !!}
                            </div>
                        @endif
                    </div>

                    {{-- Stacked Ads Section (di bawah video info, sebelum related videos) --}}
                    @if (!$skipAds)
                        <div class="stacked-ads"
                            style="display: flex; flex-direction: column; gap: 1rem; margin: 1.5rem 0;">
                            {{-- 900x250 Banner --}}
                            <div
                                style="display: flex; justify-content: center; background: rgba(30,41,59,0.3); border-radius: 12px; padding: 1rem; overflow: hidden;">
                                <div style="max-width: 900px; width: 100%;">
                                    <script async type="application/javascript" src="https://a.magsrv.com/ad-provider.js"></script>
                                    <ins class="eas6a97888e2" data-zoneid="5812378"></ins>
                                    <script>
                                        (AdProvider = window.AdProvider || []).push({
                                            "serve": {}
                                        });
                                    </script>
                                </div>
                            </div>

                            {{-- Flex row: 300x250 ads side by side --}}
                            <div style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center;">
                                <div style="background: rgba(30,41,59,0.3); border-radius: 12px; padding: 1rem;">
                                    <script async type="application/javascript" src="https://a.magsrv.com/ad-provider.js"></script>
                                    <ins class="eas6a97888e2" data-zoneid="5812370"></ins>
                                    <script>
                                        (AdProvider = window.AdProvider || []).push({
                                            "serve": {}
                                        });
                                    </script>
                                </div>
                                <div style="background: rgba(30,41,59,0.3); border-radius: 12px; padding: 1rem;">
                                    <script async type="application/javascript" src="https://a.magsrv.com/ad-provider.js"></script>
                                    <ins class="eas6a97888e6" data-zoneid="5812396"></ins>
                                    <script>
                                        (AdProvider = window.AdProvider || []).push({
                                            "serve": {}
                                        });
                                    </script>
                                </div>
                            </div>

                            {{-- Flex row: 300x600 + Zone 5812390 --}}
                            <div
                                style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: center; align-items: flex-start;">
                                <div style="background: rgba(30,41,59,0.3); border-radius: 12px; padding: 1rem;">
                                    <script async type="application/javascript" src="https://a.magsrv.com/ad-provider.js"></script>
                                    <ins class="eas6a97888e2" data-zoneid="5812380"></ins>
                                    <script>
                                        (AdProvider = window.AdProvider || []).push({
                                            "serve": {}
                                        });
                                    </script>
                                </div>
                                <div
                                    style="background: rgba(30,41,59,0.3); border-radius: 12px; padding: 1rem; flex: 1; min-width: 280px;">
                                    <script async type="application/javascript" src="https://a.magsrv.com/ad-provider.js"></script>
                                    <ins class="eas6a97888e31" data-zoneid="5812398"></ins>
                                    <script>
                                        (AdProvider = window.AdProvider || []).push({
                                            "serve": {}
                                        });
                                    </script>
                                </div>
                            </div>

                            {{-- Mobile IM --}}
                            <div
                                style="display: flex; justify-content: center; background: rgba(30,41,59,0.3); border-radius: 12px; padding: 1rem;">
                                <div style="max-width: 320px; width: 100%;">
                                    <script async type="application/javascript" src="https://a.magsrv.com/ad-provider.js"></script>
                                    <ins class="eas6a97888e14" data-zoneid="5812382"></ins>
                                    <script>
                                        (AdProvider = window.AdProvider || []).push({
                                            "serve": {}
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Related Videos Section --}}
                    <div class="related-videos-section"
                        style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
                        <h3 style="margin-bottom: 1.5rem; font-size: 1.25rem; font-weight: 600;">Video Lainnya</h3>
                        <div class="related-videos-grid"
                            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                            @foreach ($relatedVideos as $related)
                                <a href="{{ route('videos.show', $related) }}" class="related-video-card"
                                    style="display: block; background: rgba(30,41,59,0.5); border-radius: 12px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s;">
                                    <div style="position: relative;">
                                        @if ($related->thumbnail)
                                            <img src="{{ $related->getThumbnailUrl() }}" alt="{{ $related->title }}"
                                                style="width: 100%; height: 160px; object-fit: cover;">
                                        @else
                                            <video src="{{ route('videos.preview', $related) }}#t=10" muted
                                                preload="metadata" style="width: 100%; height: 160px; object-fit: cover;"
                                                onmouseover="this.play()"
                                                onmouseout="this.pause();this.currentTime=10;"></video>
                                        @endif
                                        @if ($related->duration)
                                            <span
                                                style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.8); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; color: white;">{{ gmdate('i:s', $related->duration) }}</span>
                                        @endif
                                    </div>
                                    <div style="padding: 1rem;">
                                        <div
                                            style="font-size: 0.9rem; font-weight: 500; color: white; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 0.5rem;">
                                            {{ $related->title }}</div>
                                        <div style="font-size: 0.8rem; color: #64748b;">
                                            {{ number_format($related->total_views) }} views •
                                            {{ $related->created_at->diffForHumans() }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Sidebar Ad (160x600) --}}
            @if (!$skipAds)
                <div class="sidebar-ad">
                    <script async type="application/javascript" src="https://a.magsrv.com/ad-provider.js"></script>
                    <ins class="eas6a97888e2" data-zoneid="5812376"></ins>
                    <script>
                        (AdProvider = window.AdProvider || []).push({
                            "serve": {}
                        });
                    </script>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        {{-- Plyr --}}
        <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>

        {{-- Share Button Logic --}}
        <script>
            document.getElementById('share-btn')?.addEventListener('click', async function() {
                const btn = this;
                const originalHtml = btn.innerHTML;
                const url = window.location.href;
                try {
                    await navigator.clipboard.writeText(url);
                    btn.innerHTML =
                        `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> <span style="color:var(--success)">Link Disalin!</span>`;
                } catch (err) {
                    const ta = document.createElement("textarea");
                    ta.value = url;
                    ta.style.position = "fixed";
                    ta.style.left = "-9999px";
                    document.body.appendChild(ta);
                    ta.focus();
                    ta.select();
                    try {
                        document.execCommand('copy');
                        btn.innerHTML = `<span style="color:var(--success)">Copied!</span>`;
                    } catch (e) {
                        alert('Gagal copy link');
                    }
                    document.body.removeChild(ta);
                }
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                }, 2000);
            });
        </script>



        {{-- Video Player Logic & Adblock Detection --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const videoElement = document.getElementById('player');
                const videoLoading = document.getElementById('video-loading');
                const skipAds = {{ $skipAds ? 'true' : 'false' }};

                // Promo Modals Logic
                @if (!$skipAds)
                    const promoModal1 = document.getElementById('promo-modal-1');
                    const promoModal2 = document.getElementById('promo-modal-2');

                    if (promoModal1) setTimeout(() => {
                        promoModal1.style.display = 'flex';
                    }, 2000);
                    if (promoModal2) setTimeout(() => {
                        promoModal2.style.display = 'flex';
                    }, 20000);

                    document.querySelectorAll('.close-modal').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const m = document.getElementById(this.dataset.modal);
                            if (m) m.style.display = 'none';
                        });
                    });
                    [promoModal1, promoModal2].forEach(m => {
                        if (m) m.addEventListener('click', e => {
                            if (e.target === m) m.style.display = 'none';
                        });
                    });
                @endif

                // Video Token & Player Logic
                async function getStreamToken() {
                    try {
                        const res = await fetch('{{ route('videos.token', $video) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': window.csrfToken
                            }
                        });
                        return await res.json();
                    } catch (e) {
                        console.error(e);
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
                    } catch (e) {}
                }

                async function initPlayer() {
                    if (!videoElement) return;
                    const tokenData = await getStreamToken();

                    if (!tokenData || !tokenData.token) {
                        if (videoLoading) videoLoading.innerHTML =
                            '<div style="text-align:center;color:#ef4444;"><p>Gagal memuat video</p></div>';
                        return;
                    }

                    const source = videoElement.querySelector('source');
                    if (source) {
                        source.src = tokenData.stream_url;
                        videoElement.load();
                    }

                    // VAST Pre-roll Ad Logic
                    const vastUrl = 'https://s.magsrv.com/v1/vast.php?idzone=5812386';
                    let adPlayed = skipAds;

                    async function playVastAd() {
                        if (skipAds) return Promise.resolve();

                        try {
                            // Add timeout to prevent infinite hang
                            const controller = new AbortController();
                            const timeoutId = setTimeout(() => controller.abort(), 10000);

                            const response = await fetch(vastUrl, {
                                signal: controller.signal
                            });
                            clearTimeout(timeoutId);

                            const vastXml = await response.text();
                            const parser = new DOMParser();
                            const xmlDoc = parser.parseFromString(vastXml, 'text/xml');

                            // Get media file from VAST
                            const mediaFile = xmlDoc.querySelector('MediaFile');
                            const clickThrough = xmlDoc.querySelector('ClickThrough');
                            const impressions = xmlDoc.querySelectorAll('Impression');

                            // If no MediaFile found, just resolve and continue to main video
                            if (!mediaFile || !mediaFile.textContent.trim()) {
                                console.log('No VAST ad available, skipping to main video');
                                return Promise.resolve();
                            }

                            const adVideoUrl = mediaFile.textContent.trim();
                            const adClickUrl = clickThrough ? clickThrough.textContent.trim() : null;

                            // Track impressions
                            impressions.forEach(imp => {
                                const impUrl = imp.textContent.trim();
                                if (impUrl) {
                                    const img = new Image();
                                    img.src = impUrl;
                                }
                            });

                            return new Promise((resolve) => {
                                const videoContainer = document.getElementById('video-container');
                                if (!videoContainer) {
                                    console.log('Video container not found, skipping ad');
                                    resolve();
                                    return;
                                }

                                // Create ad overlay
                                const adOverlay = document.createElement('div');
                                adOverlay.id = 'vast-ad-overlay';
                                adOverlay.style.cssText =
                                    'position:absolute;top:0;left:0;width:100%;height:100%;z-index:100;background:#000;';

                                const adVideo = document.createElement('video');
                                adVideo.src = adVideoUrl;
                                adVideo.style.cssText =
                                    'width:100%;height:100%;object-fit:contain;';
                                adVideo.autoplay = true;
                                adVideo.playsInline = true;
                                adVideo.muted = false;

                                // Skip button (appears after 5 seconds)
                                const skipBtn = document.createElement('button');
                                skipBtn.textContent = 'Skip Ad';
                                skipBtn.style.cssText =
                                    'position:absolute;bottom:20px;right:20px;background:rgba(0,0,0,0.8);color:white;border:1px solid white;padding:10px 20px;border-radius:4px;cursor:pointer;font-size:14px;display:none;z-index:101;';

                                // Ad label
                                const adLabel = document.createElement('div');
                                adLabel.textContent = 'Iklan';
                                adLabel.style.cssText =
                                    'position:absolute;top:10px;left:10px;background:rgba(255,204,0,0.9);color:#000;padding:4px 12px;border-radius:4px;font-size:12px;font-weight:bold;z-index:101;';

                                // Cleanup function
                                function cleanup() {
                                    if (adOverlay.parentNode) {
                                        adOverlay.remove();
                                    }
                                    resolve();
                                }

                                // Click handler for ad
                                if (adClickUrl) {
                                    adVideo.style.cursor = 'pointer';
                                    adVideo.addEventListener('click', () => {
                                        window.open(adClickUrl, '_blank');
                                    });
                                }

                                // Show skip button after 5 seconds
                                setTimeout(() => {
                                    skipBtn.style.display = 'block';
                                }, 5000);

                                // Fallback timeout - if ad takes too long, skip it
                                setTimeout(() => {
                                    console.log('Ad timeout, skipping');
                                    cleanup();
                                }, 30000);

                                skipBtn.addEventListener('click', cleanup);
                                adVideo.addEventListener('ended', cleanup);
                                adVideo.addEventListener('error', () => {
                                    console.log('Ad video error, skipping');
                                    cleanup();
                                });

                                adOverlay.appendChild(adVideo);
                                adOverlay.appendChild(skipBtn);
                                adOverlay.appendChild(adLabel);
                                videoContainer.appendChild(adOverlay);

                                if (videoLoading) videoLoading.style.display = 'none';
                            });
                        } catch (e) {
                            console.log('VAST ad error:', e);
                            return Promise.resolve(); // Always resolve to allow main video to play
                        }
                    }

                    try {
                        const player = new Plyr('#player', {
                            controls: ['play-large', 'play', 'progress', 'current-time', 'duration', 'mute',
                                'volume', 'captions', 'settings', 'pip', 'fullscreen'
                            ],
                            settings: ['quality', 'speed', 'loop'],
                            ratio: '16:9',
                            speed: {
                                selected: 1,
                                options: [0.5, 0.75, 1, 1.25, 1.5, 2]
                            }
                        });
                        player.on('ready', () => {
                            if (videoLoading) videoLoading.style.display = 'none';
                        });

                        // Trigger VAST ad on first play - fix infinite loop
                        let adWatchedConfirmed = false;
                        player.on('play', async () => {
                            if (!adPlayed && !skipAds) {
                                adPlayed = true; // Set immediately to prevent re-trigger
                                player.pause();
                                await playVastAd();
                                player.play();
                                return; // Exit early, next play will handle confirmAdWatched
                            }
                            if (!skipAds && tokenData.token && !adWatchedConfirmed) {
                                adWatchedConfirmed = true;
                                confirmAdWatched(tokenData.token);
                            }
                        });
                    } catch (e) {
                        console.error('Plyr failed', e);
                        videoElement.controls = true;
                        if (videoLoading) videoLoading.style.display = 'none';
                    }
                }
                initPlayer();

                // Adblock Detection & Alternative Monetization
                @if (!$skipAds)
                    (async function() {
                        function detectAdblock() {
                            return new Promise(resolve => {
                                if (typeof aclib === 'undefined') return resolve(true);
                                const bait = document.createElement('div');
                                bait.className = 'adsbox pub_728x90';
                                bait.style.cssText =
                                    'width:1px!important;height:1px!important;position:absolute!important;left:-10000px!important;';
                                document.body.appendChild(bait);
                                setTimeout(() => {
                                    const blocked = bait.offsetHeight === 0 || window
                                        .getComputedStyle(bait).display === 'none';
                                    bait.remove();
                                    resolve(blocked);
                                }, 100);
                            });
                        }

                        async function loadAltMonetization() {
                            try {
                                const res = await fetch('{{ route('adblock.check') }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': window.csrfToken
                                    }
                                });
                                const data = await res.json();
                                if (data.success && data.content) {
                                    const c = document.getElementById('adblock-monetization-container');
                                    if (c) {
                                        c.innerHTML = data.content;
                                        c.style.display = 'block';
                                        c.querySelectorAll('script').forEach(s => {
                                            const ns = document.createElement('script');
                                            if (s.src) ns.src = s.src;
                                            else ns.textContent = s.textContent;
                                            document.head.appendChild(ns);
                                        });
                                    }
                                }
                            } catch (e) {}
                        }

                        setTimeout(async () => {
                            if (await detectAdblock()) {
                                console.log('Adblock detected');
                                loadAltMonetization();
                            }
                        }, 2000); // Delay agak lama agar aclib sempat load
                    })();
                @endif
            });
        </script>
        <script type="application/javascript">
(function() {
    function randStr(e,t){for(var n="",r=t||"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz",o=0;o<e;o++)n+=r.charAt(Math.floor(Math.random()*r.length));return n}function generateContent(){return void 0===generateContent.val&&(generateContent.val="document.dispatchEvent("+randStr(4*Math.random()+3)+");"),generateContent.val}try{Object.defineProperty(document.currentScript,"innerHTML",{get:generateContent}),Object.defineProperty(document.currentScript,"textContent",{get:generateContent})}catch(e){};

    //version 7.0.0

    var adConfig = {
    "ads_host": "a.pemsrv.com",
    "syndication_host": "s.pemsrv.com",
    "idzone": 5812394,
    "popup_fallback": false,
    "popup_force": false,
    "chrome_enabled": true,
    "new_tab": false,
    "frequency_period": 180,
    "frequency_count": 1,
    "trigger_method": 1,
    "trigger_class": "",
    "trigger_delay": 0,
    "capping_enabled": true,
    "tcf_enabled": true,
    "only_inline": false
};

window.document.querySelectorAll||(document.querySelectorAll=document.body.querySelectorAll=Object.querySelectorAll=function(e,o,t,i,n){var r=document,a=r.createStyleSheet();for(n=r.all,o=[],t=(e=e.replace(/\[for\b/gi,"[htmlFor").split(",")).length;t--;){for(a.addRule(e[t],"k:v"),i=n.length;i--;)n[i].currentStyle.k&&o.push(n[i]);a.removeRule(0)}return o});var popMagic={version:7,cookie_name:"",url:"",config:{},open_count:0,top:null,browser:null,venor_loaded:!1,venor:!1,tcfData:null,configTpl:{ads_host:"",syndication_host:"",idzone:"",frequency_period:720,frequency_count:1,trigger_method:1,trigger_class:"",popup_force:!1,popup_fallback:!1,chrome_enabled:!0,new_tab:!1,cat:"",tags:"",el:"",sub:"",sub2:"",sub3:"",only_inline:!1,trigger_delay:0,capping_enabled:!0,tcf_enabled:!1,cookieconsent:!0,should_fire:function(){return!0},on_redirect:null},init:function(e){if(void 0!==e.idzone&&e.idzone){void 0===e.customTargeting&&(e.customTargeting=[]),window.customTargeting=e.customTargeting||null;var o=Object.keys(e.customTargeting).filter(function(e){return e.search("ex_")>=0});for(var t in o.length&&o.forEach(function(e){return this.configTpl[e]=null}.bind(this)),this.configTpl)Object.prototype.hasOwnProperty.call(this.configTpl,t)&&(void 0!==e[t]?this.config[t]=e[t]:this.config[t]=this.configTpl[t]);if(void 0!==this.config.idzone&&""!==this.config.idzone){!0!==this.config.only_inline&&this.loadHosted();var i=this;this.checkTCFConsent(function(){"complete"===document.readyState?i.preparePopWait():i.addEventToElement(window,"load",i.preparePop)})}}},getCountFromCookie:function(){if(!this.config.cookieconsent)return 0;var e=popMagic.getCookie(popMagic.cookie_name),o=void 0===e?0:parseInt(e);return isNaN(o)&&(o=0),o},getLastOpenedTimeFromCookie:function(){var e=popMagic.getCookie(popMagic.cookie_name),o=null;if(void 0!==e){var t=e.split(";")[1];o=t>0?parseInt(t):0}return isNaN(o)&&(o=null),o},shouldShow:function(e){if(e=e||!1,!popMagic.config.capping_enabled){var o=!0,t=popMagic.config.should_fire;try{e||"function"!=typeof t||(o=Boolean(t()))}catch(e){console.error("Error executing should fire callback function:",e)}return o&&0===popMagic.open_count}if(popMagic.open_count>=popMagic.config.frequency_count)return!1;var i=popMagic.getCountFromCookie(),n=popMagic.getLastOpenedTimeFromCookie(),r=Math.floor(Date.now()/1e3),a=n+popMagic.config.trigger_delay;return!(n&&a>r)&&(popMagic.open_count=i,!(i>=popMagic.config.frequency_count))},venorShouldShow:function(){return popMagic.venor_loaded&&"0"===popMagic.venor},setAsOpened:function(e){var o=e?e.target||e.srcElement:null,t={id:"",tagName:"",classes:"",text:"",href:"",elm:""};void 0!==o&&null!=o&&(t={id:void 0!==o.id&&null!=o.id?o.id:"",tagName:void 0!==o.tagName&&null!=o.tagName?o.tagName:"",classes:void 0!==o.classList&&null!=o.classList?o.classList:"",text:void 0!==o.outerText&&null!=o.outerText?o.outerText:"",href:void 0!==o.href&&null!=o.href?o.href:"",elm:o});var i=new CustomEvent("creativeDisplayed-"+popMagic.config.idzone,{detail:t});if(document.dispatchEvent(i),popMagic.config.capping_enabled){var n=1;n=0!==popMagic.open_count?popMagic.open_count+1:popMagic.getCountFromCookie()+1;var r=Math.floor(Date.now()/1e3);popMagic.config.cookieconsent&&popMagic.setCookie(popMagic.cookie_name,n+";"+r,popMagic.config.frequency_period)}else++popMagic.open_count},loadHosted:function(){var e=document.createElement("script");for(var o in e.type="application/javascript",e.async=!0,e.src="//"+this.config.ads_host+"/popunder1000.js",e.id="popmagicldr",this.config)Object.prototype.hasOwnProperty.call(this.config,o)&&"ads_host"!==o&&"syndication_host"!==o&&e.setAttribute("data-exo-"+o,this.config[o]);var t=document.getElementsByTagName("body").item(0);t.firstChild?t.insertBefore(e,t.firstChild):t.appendChild(e)},preparePopWait:function(){setTimeout(popMagic.preparePop,400)},preparePop:function(){if("object"!=typeof exoJsPop101||!Object.prototype.hasOwnProperty.call(exoJsPop101,"add")){if(popMagic.top=self,popMagic.top!==self)try{top.document.location.toString()&&(popMagic.top=top)}catch(e){}if(popMagic.cookie_name="zone-cap-"+popMagic.config.idzone,popMagic.config.capping_enabled||(document.cookie=popMagic.cookie_name+"=;expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/"),popMagic.shouldShow(!0)){var e=new XMLHttpRequest;e.onreadystatechange=function(){e.readyState==XMLHttpRequest.DONE&&(popMagic.venor_loaded=!0,200==e.status?popMagic.venor=e.responseText:popMagic.venor="0")};var o="https:"!==document.location.protocol&&"http:"!==document.location.protocol?"https:":document.location.protocol;e.open("GET",o+"//"+popMagic.config.syndication_host+"/venor.php",!0);try{e.send()}catch(e){popMagic.venor_loaded=!0}}if(popMagic.buildUrl(),popMagic.browser=popMagic.browserDetector.getBrowserInfo(),popMagic.config.chrome_enabled||!popMagic.browser.isChrome){var t=popMagic.getPopMethod(popMagic.browser);popMagic.addEvent("click",t)}}},getPopMethod:function(e){return popMagic.config.popup_force||popMagic.config.popup_fallback&&e.isChrome&&e.version>=68&&!e.isMobile?popMagic.methods.popup:e.isMobile?popMagic.methods.default:e.isChrome?popMagic.methods.chromeTab:popMagic.methods.default},checkTCFConsent:function(e){if(this.config.tcf_enabled&&"function"==typeof window.__tcfapi){var o=this;window.__tcfapi("addEventListener",2,function(t,i){i&&(o.tcfData=t,"tcloaded"!==t.eventStatus&&"useractioncomplete"!==t.eventStatus||(window.__tcfapi("removeEventListener",2,function(){},t.listenerId),e()))})}else e()},buildUrl:function(){var e,o="https:"!==document.location.protocol&&"http:"!==document.location.protocol?"https:":document.location.protocol,t=top===self?document.URL:document.referrer,i={type:"inline",name:"popMagic",ver:this.version},n="";customTargeting&&Object.keys(customTargeting).length&&("object"==typeof customTargeting?Object.keys(customTargeting):customTargeting).forEach(function(o){"object"==typeof customTargeting?e=customTargeting[o]:Array.isArray(customTargeting)&&(e=scriptEl.getAttribute(o));var t=o.replace("data-exo-","");n+="&"+t+"="+e});var r=this.tcfData&&this.tcfData.gdprApplies&&!0===this.tcfData.gdprApplies?1:0;this.url=o+"//"+this.config.syndication_host+"/v1/link.php?cat="+this.config.cat+"&idzone="+this.config.idzone+"&type=8&p="+encodeURIComponent(t)+"&sub="+this.config.sub+(""!==this.config.sub2?"&sub2="+this.config.sub2:"")+(""!==this.config.sub3?"&sub3="+this.config.sub3:"")+"&block=1&el="+this.config.el+"&tags="+this.config.tags+"&scr_info="+function(e){var o=e.type+"|"+e.name+"|"+e.ver;return encodeURIComponent(btoa(o))}(i)+n+"&gdpr="+r+"&cb="+Math.floor(1e9*Math.random()),this.tcfData&&this.tcfData.tcString?this.url+="&gdpr_consent="+encodeURIComponent(this.tcfData.tcString):this.url+="&cookieconsent="+this.config.cookieconsent},addEventToElement:function(e,o,t){e.addEventListener?e.addEventListener(o,t,!1):e.attachEvent?(e["e"+o+t]=t,e[o+t]=function(){e["e"+o+t](window.event)},e.attachEvent("on"+o,e[o+t])):e["on"+o]=e["e"+o+t]},getTriggerClasses:function(){var e,o=[];-1===popMagic.config.trigger_class.indexOf(",")?e=popMagic.config.trigger_class.split(" "):e=popMagic.config.trigger_class.replace(/\s/g,"").split(",");for(var t=0;t<e.length;t++)""!==e[t]&&o.push("."+e[t]);return o},addEvent:function(e,o){var t;if("3"!=popMagic.config.trigger_method)if("2"!=popMagic.config.trigger_method||""==popMagic.config.trigger_class)if("4"!=popMagic.config.trigger_method||""==popMagic.config.trigger_class)if("5"!=popMagic.config.trigger_method||""==popMagic.config.trigger_class)popMagic.addEventToElement(document,e,o);else{var i="a"+popMagic.getTriggerClasses().map(function(e){return":not("+e+")"}).join("");t=document.querySelectorAll(i);for(var n=0;n<t.length;n++)popMagic.addEventToElement(t[n],e,o)}else{var r=popMagic.getTriggerClasses();popMagic.addEventToElement(document,e,function(e){r.some(function(o){return null!==e.target.closest(o)})||o.call(e.target,e)})}else{var a=popMagic.getTriggerClasses();for(t=document.querySelectorAll(a.join(", ")),n=0;n<t.length;n++)popMagic.addEventToElement(t[n],e,o)}else for(t=document.querySelectorAll("a"),n=0;n<t.length;n++)popMagic.addEventToElement(t[n],e,o)},setCookie:function(e,o,t){if(!this.config.cookieconsent)return!1;t=parseInt(t,10);var i=new Date;i.setMinutes(i.getMinutes()+parseInt(t));var n=encodeURIComponent(o)+"; expires="+i.toUTCString()+"; path=/";document.cookie=e+"="+n},getCookie:function(e){if(!this.config.cookieconsent)return!1;var o,t,i,n=document.cookie.split(";");for(o=0;o<n.length;o++)if(t=n[o].substr(0,n[o].indexOf("=")),i=n[o].substr(n[o].indexOf("=")+1),(t=t.replace(/^\s+|\s+$/g,""))===e)return decodeURIComponent(i)},randStr:function(e,o){for(var t="",i=o||"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789",n=0;n<e;n++)t+=i.charAt(Math.floor(Math.random()*i.length));return t},isValidUserEvent:function(e){return!(!("isTrusted"in e)||!e.isTrusted||"ie"===popMagic.browser.name||"safari"===popMagic.browser.name)||0!=e.screenX&&0!=e.screenY},isValidHref:function(e){if(void 0===e||""==e)return!1;return!/\s?javascript\s?:/i.test(e)},findLinkToOpen:function(e){var o=e,t=!1;try{for(var i=0;i<20&&!o.getAttribute("href")&&o!==document&&"html"!==o.nodeName.toLowerCase();)o=o.parentNode,i++;var n=o.getAttribute("target");n&&-1!==n.indexOf("_blank")||(t=o.getAttribute("href"))}catch(e){}return popMagic.isValidHref(t)||(t=!1),t||window.location.href},getPuId:function(){return"ok_"+Math.floor(89999999*Math.random()+1e7)},executeOnRedirect:function(){try{popMagic.config.capping_enabled||"function"!=typeof popMagic.config.on_redirect||popMagic.config.on_redirect()}catch(e){console.error("Error executing on redirect callback:",e)}},browserDetector:{browserDefinitions:[["firefox",/Firefox\/([0-9.]+)(?:\s|$)/],["opera",/Opera\/([0-9.]+)(?:\s|$)/],["opera",/OPR\/([0-9.]+)(:?\s|$)$/],["edge",/Edg(?:e|)\/([0-9._]+)/],["ie",/Trident\/7\.0.*rv:([0-9.]+)\).*Gecko$/],["ie",/MSIE\s([0-9.]+);.*Trident\/[4-7].0/],["ie",/MSIE\s(7\.0)/],["safari",/Version\/([0-9._]+).*Safari/],["chrome",/(?!Chrom.*Edg(?:e|))Chrom(?:e|ium)\/([0-9.]+)(:?\s|$)/],["chrome",/(?!Chrom.*OPR)Chrom(?:e|ium)\/([0-9.]+)(:?\s|$)/],["bb10",/BB10;\sTouch.*Version\/([0-9.]+)/],["android",/Android\s([0-9.]+)/],["ios",/Version\/([0-9._]+).*Mobile.*Safari.*/],["yandexbrowser",/YaBrowser\/([0-9._]+)/],["crios",/CriOS\/([0-9.]+)(:?\s|$)/]],isChromeOrChromium:function(){var e=window.navigator,o=(e.userAgent||"").toLowerCase(),t=e.vendor||"";if(-1!==o.indexOf("crios"))return!0;if(e.userAgentData&&Array.isArray(e.userAgentData.brands)&&e.userAgentData.brands.length>0){var i=e.userAgentData.brands,n=i.some(function(e){return"Google Chrome"===e.brand}),r=i.some(function(e){return"Chromium"===e.brand})&&2===i.length;return n||r}var a=!!window.chrome,c=-1!==o.indexOf("edg"),p=!!window.opr||-1!==o.indexOf("opr"),s=!(!e.brave||!e.brave.isBrave),g=-1!==o.indexOf("vivaldi"),l=-1!==o.indexOf("yabrowser"),d=-1!==o.indexOf("samsungbrowser"),u=-1!==o.indexOf("ucbrowser");return a&&"Google Inc."===t&&!c&&!p&&!s&&!g&&!l&&!d&&!u},getBrowserInfo:function(){var e=window.navigator.userAgent,o={name:"other",version:"1.0",versionNumber:1,isChrome:this.isChromeOrChromium(),isMobile:!!e.match(/Android|BlackBerry|iPhone|iPad|iPod|Opera Mini|IEMobile|WebOS|Windows Phone/i)};for(var t in this.browserDefinitions){var i=this.browserDefinitions[t];if(i[1].test(e)){var n=i[1].exec(e),r=n&&n[1].split(/[._]/).slice(0,3),a=Array.prototype.slice.call(r,1).join("")||"0";r&&r.length<3&&Array.prototype.push.apply(r,1===r.length?[0,0]:[0]),o.name=i[0],o.version=r.join("."),o.versionNumber=parseFloat(r[0]+"."+a);break}}return o}},methods:{default:function(e){if(!popMagic.shouldShow()||!popMagic.venorShouldShow()||!popMagic.isValidUserEvent(e))return!0;var o=e.target||e.srcElement,t=popMagic.findLinkToOpen(o);return window.open(t,"_blank"),popMagic.setAsOpened(e),popMagic.executeOnRedirect(),popMagic.top.document.location=popMagic.url,void 0!==e.preventDefault&&(e.preventDefault(),e.stopPropagation()),!0},chromeTab:function(e){if(!popMagic.shouldShow()||!popMagic.venorShouldShow()||!popMagic.isValidUserEvent(e))return!0;if(void 0===e.preventDefault)return!0;e.preventDefault(),e.stopPropagation();var o=top.window.document.createElement("a"),t=e.target||e.srcElement;o.href=popMagic.findLinkToOpen(t),document.getElementsByTagName("body")[0].appendChild(o);var i=new MouseEvent("click",{bubbles:!0,cancelable:!0,view:window,screenX:0,screenY:0,clientX:0,clientY:0,ctrlKey:!0,altKey:!1,shiftKey:!1,metaKey:!0,button:0});i.preventDefault=void 0,o.dispatchEvent(i),o.parentNode.removeChild(o),popMagic.executeOnRedirect(),window.open(popMagic.url,"_self"),popMagic.setAsOpened(e)},popup:function(e){if(!popMagic.shouldShow()||!popMagic.venorShouldShow()||!popMagic.isValidUserEvent(e))return!0;var o="";if(popMagic.config.popup_fallback&&!popMagic.config.popup_force){var t=Math.max(Math.round(.8*window.innerHeight),300);o="menubar=1,resizable=1,width="+Math.max(Math.round(.7*window.innerWidth),300)+",height="+t+",top="+(window.screenY+100)+",left="+(window.screenX+100)}var i=document.location.href,n=window.open(i,popMagic.getPuId(),o);popMagic.setAsOpened(e),setTimeout(function(){n.location.href=popMagic.url,popMagic.executeOnRedirect()},200),void 0!==e.preventDefault&&(e.preventDefault(),e.stopPropagation())}}};    popMagic.init(adConfig);
})();


</script>
        {{-- <script id="aclib" type="text/javascript" src="//acscdn.com/script/aclib.js"></script>
        <script type="text/javascript">
            aclib.runAutoTag({
                zoneId: 'igfjjlmgoi',
            });
        </script> --}}
    @endpush
@endsection
