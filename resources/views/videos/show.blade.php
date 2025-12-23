@extends('layouts.app')

@section('title', $video->title)

@section('content')
    <div class="video-page" style="display: flex; flex-direction: column; gap: 2rem;">
        {{-- Main Content Area with Sidebar Ads --}}
        <div class="video-content-wrapper" style="display: grid; grid-template-columns: 1fr 180px; gap: 1.5rem;">
            <div class="video-main">
                <div class="video-wrapper"
                    style="position: relative; background: #000; border-radius: 16px; overflow: hidden;">
                    {{-- Video Player --}}
                    <video id="video-player" controls poster="{{ $video->getThumbnailUrl() }}"
                        style="width: 100%; max-height: 70vh; display: none;">
                        <source src="" type="{{ $video->mime_type }}">
                        Browser tidak support video.
                    </video>

                    {{-- Loading State --}}
                    <div id="video-loading"
                        style="display: flex; align-items: center; justify-content: center; min-height: 400px; background: #0f172a;">
                        <div style="text-align: center;">
                            <div
                                style="width: 50px; height: 50px; border: 3px solid rgba(99,102,241,0.3); border-top-color: #6366f1; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem;">
                            </div>
                            <p style="color: #64748b;">Memuat video...</p>
                        </div>
                    </div>
                </div>

                {{-- Banner Ad 728x90 - Below Video - Zone 10743066 --}}
                @if (!$skipAds)
                    <div class="ad-banner-horizontal" style="margin: 1.5rem 0; display: flex; justify-content: center;">
                        <div id="banner-728x90"
                            style="min-height: 90px; background: rgba(15,23,42,0.5); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <script type="text/javascript">
                                var defined = window.defined || {
                                    ads: function(a, b) {}
                                };
                                defined.ads('banner-728x90', 10743066);
                            </script>
                        </div>
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
                    </div>

                    @if ($video->description)
                        <div style="margin-top: 1rem; color: #cbd5e1; line-height: 1.7;">
                            {!! nl2br(e($video->description)) !!}
                        </div>
                    @endif
                </div>

                {{-- Native Ad - Zone 10743074 --}}
                @if (!$skipAds)
                    <div class="ad-native"
                        style="margin: 1.5rem 0; padding: 1rem; background: rgba(30,41,59,0.5); border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <span
                                style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Sponsored</span>
                        </div>
                        <div id="native-ad" style="min-height: 100px;">
                            <script type="text/javascript">
                                var defined = window.defined || {
                                    ads: function(a, b) {}
                                };
                                defined.ads('native-ad', 10743074);
                            </script>
                        </div>
                    </div>
                @endif

                @if (!$skipAds)
                    <div class="card"
                        style="background: linear-gradient(135deg, rgba(99,102,241,0.1), rgba(14,165,233,0.1)); text-align: center; margin-top: 1.5rem;">
                        <p style="margin-bottom: 1rem;">Tidak ingin melihat iklan?</p>
                        <a href="{{ route('memberships.index') }}" class="btn btn-primary">Jadi Member Sekarang</a>
                    </div>
                @endif
            </div>

            {{-- Sidebar - Banner 160x600 - Zone 10743082 --}}
            @if (!$skipAds)
                <div class="ad-sidebar" style="position: sticky; top: 100px; height: fit-content;">
                    <div id="banner-160x600"
                        style="min-height: 600px; background: rgba(15,23,42,0.5); border-radius: 8px; display: flex; align-items: flex-start; justify-content: center; padding-top: 1rem;">
                        <script type="text/javascript">
                            var defined = window.defined || {
                                ads: function(a, b) {}
                            };
                            defined.ads('banner-160x600', 10743082);
                        </script>
                    </div>
                </div>
            @endif
        </div>

        {{-- Video Lainnya Section - Now at Bottom --}}
        <div class="related-videos-section"
            style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1);">
            <h3 style="margin-bottom: 1.5rem; font-size: 1.25rem; font-weight: 600;">Video Lainnya</h3>
            <div class="related-videos-grid"
                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem;">
                @foreach ($relatedVideos as $related)
                    <a href="{{ route('videos.show', $related) }}" class="related-video-card"
                        style="display: block; background: rgba(30,41,59,0.5); border-radius: 12px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s;"
                        onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.3)'"
                        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                        <div style="position: relative;">
                            <img src="{{ $related->getThumbnailUrl() }}" alt="{{ $related->title }}"
                                style="width: 100%; height: 160px; object-fit: cover;">
                            @if ($related->duration)
                                <span
                                    style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.8); padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem; color: white;">
                                    {{ gmdate('i:s', $related->duration) }}
                                </span>
                            @endif
                        </div>
                        <div style="padding: 1rem;">
                            <div
                                style="font-size: 0.9rem; font-weight: 500; color: white; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-bottom: 0.5rem;">
                                {{ $related->title }}
                            </div>
                            <div style="font-size: 0.8rem; color: #64748b;">
                                {{ number_format($related->total_views) }} views •
                                {{ $related->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- In-Page Push Ad - Zone 10743058 --}}
        @if (!$skipAds)
            <div id="inpage-push-ad" style="display: none;">
                <script type="text/javascript">
                    var defined = window.defined || {
                        ads: function(a, b) {}
                    };
                    defined.ads('inpage-push-ad', 10743058);
                </script>
            </div>
        @endif
    </div>

    @push('styles')
        <style>
            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }

            @media (max-width: 1024px) {
                .video-content-wrapper {
                    grid-template-columns: 1fr !important;
                }

                .ad-sidebar {
                    display: none !important;
                }

                .ad-banner-horizontal {
                    overflow-x: auto;
                }
            }

            @media (max-width: 768px) {
                .related-videos-grid {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>
    @endpush

    @push('scripts')
        {{-- Adcash Loader Script --}}
        <script src="https://js.wpadmngr.com/static/adManager.js" async></script>
        <script type="text/javascript">
            var defined = window.defined || {
                ads: function(a, b) {}
            };
        </script>

        {{-- Adcash Autotag - Zone o1bir8ndhl --}}
        <script type="text/javascript" src="https://js.wpadmngr.com/t/o1bir8ndhl.js" async></script>

        {{-- Video Slider Ad Script - Zone 10743038 --}}
        <script type="text/javascript" src="https://js.wpadmngr.com/v/10743038.js" async></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const video = document.getElementById('video-player');
                const videoLoading = document.getElementById('video-loading');
                const skipAds = {{ $skipAds ? 'true' : 'false' }};

                let streamToken = null;

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

                    streamToken = tokenData.token;
                    video.querySelector('source').src = tokenData.stream_url;
                    video.load();

                    // Langsung tampilkan video
                    if (videoLoading) videoLoading.style.display = 'none';
                    video.style.display = 'block';

                    // Confirm ad watched untuk non-member
                    if (!skipAds && streamToken) {
                        confirmAdWatched(streamToken);
                    }
                }

                // Show interstitial on page load for non-members - Zone 10743050
                @if (!$skipAds)
                    setTimeout(function() {
                        if (typeof defined !== 'undefined' && defined.ads) {
                            defined.ads('interstitial', 10743050);
                        }
                    }, 2000);
                @endif

                initPlayer();
            });
        </script>
    @endpush
@endsection
