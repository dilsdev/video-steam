@extends('layouts.app')

@section('title', $video->title)

@section('content')
    @push('styles')
        <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
        <style>
            /* Fix container iklan agar tidak collapse (meningkatkan pendapatan) */
            /* Fix container iklan agar rapi & center */
            .ad-banner-container {
                display: flex;
                justify-content: center;
                align-items: center;
                width: 100%;
                margin: 1.5rem 0;
                overflow: hidden;
                /* Hapus background agar terlihat clean */
                background: transparent;
            }

            .ad-sidebar-container {
                min-height: 600px;
                width: 160px;
                /* Center sidebar ad */
                margin: 0 auto;
                display: flex;
                justify-content: center;
            }

            /* RESPONSIVE FIX: Gunakan minmax(0, 1fr) agar konten tidak meluber dari grid */
            .video-content-wrapper {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 180px;
                gap: 1.5rem;
            }

            @media (max-width: 1024px) {
                .video-content-wrapper {
                    grid-template-columns: 1fr !important;
                }

                .ad-sidebar {
                    display: none !important;
                }
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

    <div class="video-page" style="display: flex; flex-direction: column; gap: 2rem;">
        <div class="video-content-wrapper">
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

                {{-- Banner Ad 728x90 (Top) --}}
                @if (!$skipAds)
                    <div class="ad-banner-container">
                        <x-adcash type="banner_728x90" />
                    </div>
                @endif

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

                {{-- Native Ad --}}
                @if (!$skipAds)
                    <div
                        style="margin: 2rem 0; padding: 1.5rem 0; border-top: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;">
                            <span
                                style="font-size: 0.75rem; color: #94a3b8; font-weight: 500; text-transform: uppercase; letter-spacing: 0.05em;">Sponsored
                                Content</span>
                        </div>
                        <x-adcash type="native" />
                    </div>
                @endif

                {{-- Member CTA --}}
                @if (!$skipAds)
                    <div class="card"
                        style="background: linear-gradient(135deg, rgba(99,102,241,0.15), rgba(14,165,233,0.15)); text-align: center; margin-top: 1.5rem; border: 1px solid rgba(99,102,241,0.3);">
                        <div style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem;">😤 Capek Iklan?</div>
                        <p style="margin-bottom: 1rem; color: #94a3b8;"><strong style="color: #10b981;">Rp20.000</strong> =
                            1 Bulan Nonton Tanpa Iklan</p>
                        <a href="{{ route('memberships.index') }}" class="btn btn-primary">Daftar Sekarang</a>
                    </div>
                @endif
            </div>

            {{-- Sidebar Banner 160x600 --}}
            @if (!$skipAds)
                <div class="ad-sidebar" style="position: sticky; top: 100px; height: fit-content;">
                    <div class="ad-sidebar-container">
                        <x-adcash type="banner_160x600" />
                    </div>
                </div>
            @endif
        </div>

        {{-- Video Lainnya --}}
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
                                <video src="{{ route('videos.preview', $related) }}#t=10" muted preload="metadata"
                                    style="width: 100%; height: 160px; object-fit: cover;" onmouseover="this.play()"
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
                            <div style="font-size: 0.8rem; color: #64748b;">{{ number_format($related->total_views) }}
                                views • {{ $related->created_at->diffForHumans() }}</div>
                        </div>
                    </a>
                @endforeach
            </div>
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

                    try {
                        const player = new Plyr('#player', {
                            controls: ['play-large', 'play', 'progress', 'current-time', 'duration', 'mute',
                                'volume', 'captions', 'settings', 'pip', 'fullscreen'
                            ],
                            settings: ['quality', 'speed', 'loop'],
                            ratio: '16:9', // IMPORTANT: Fix for responsiveness/CLS
                            speed: {
                                selected: 1,
                                options: [0.5, 0.75, 1, 1.25, 1.5, 2]
                            }
                        });
                        player.on('ready', () => {
                            if (videoLoading) videoLoading.style.display = 'none';
                        });
                        player.on('play', () => {
                            if (!skipAds && tokenData.token) confirmAdWatched(tokenData.token);
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
    @endpush
@endsection
