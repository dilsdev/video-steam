@extends('layouts.app')

@section('title', $video->title)

@section('content')
    <div class="video-page" style="display: flex; flex-direction: column; gap: 2rem;">
        {{-- Main Content Area with Sidebar Ads --}}
        <div class="video-content-wrapper" style="display: grid; grid-template-columns: 1fr 180px; gap: 1.5rem;">
            <div class="video-main">
                <div class="video-wrapper"
                    style="position: relative; background: #000; border-radius: 16px; overflow: hidden;">
                    {{-- Video.js Player with VAST support --}}
                    <video id="video-player" class="video-js vjs-big-play-centered vjs-theme-fantasy"
                        poster="{{ $video->getThumbnailUrl() }}" style="width: 100%; max-height: 70vh;">
                        <source src="" type="{{ $video->mime_type }}">
                        Browser tidak support video.
                    </video>

                    {{-- Loading State --}}
                    <div id="video-loading"
                        style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; background: #0f172a; z-index: 5;">
                        <div style="text-align: center;">
                            <div
                                style="width: 50px; height: 50px; border: 3px solid rgba(99,102,241,0.3); border-top-color: #6366f1; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem;">
                            </div>
                            <p style="color: #64748b;">Memuat video...</p>
                        </div>
                    </div>
                </div>

                {{-- Banner Ad 728x90 - Below Video --}}
                @if (!$skipAds)
                    <div class="ad-banner-horizontal" style="margin: 1.5rem 0; display: flex; justify-content: center;">
                        <div id="banner-728x90"
                            style="min-height: 90px; background: rgba(15,23,42,0.5); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                            <script type="text/javascript">
                                aclib.runBanner({
                                    zoneId: '10743066',
                                });
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

                {{-- Native Ad --}}
                @if (!$skipAds)
                    <div class="ad-native"
                        style="margin: 1.5rem 0; padding: 1rem; background: rgba(30,41,59,0.5); border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <span
                                style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Sponsored</span>
                        </div>
                        <div id="awn-z10743074"></div>
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

            {{-- Sidebar - Banner 160x600 --}}
            @if (!$skipAds)
                <div class="ad-sidebar" style="position: sticky; top: 100px; height: fit-content;">
                    <div id="banner-160x600"
                        style="min-height: 600px; background: rgba(15,23,42,0.5); border-radius: 8px; display: flex; align-items: flex-start; justify-content: center; padding-top: 1rem;">
                        <script type="text/javascript">
                            aclib.runBanner({
                                zoneId: '10743082',
                            });
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
    </div>

    @push('styles')
        {{-- Video.js CSS --}}
        <link href="https://vjs.zencdn.net/8.10.0/video-js.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/videojs-ima@2.3.0/dist/videojs.ima.css" rel="stylesheet" />
        <style>
            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }

            /* Video.js Custom Theme */
            .video-js {
                font-family: 'Inter', sans-serif;
            }

            .video-js .vjs-big-play-button {
                background: linear-gradient(135deg, #6366f1, #0ea5e9);
                border: none;
                border-radius: 50%;
                width: 80px;
                height: 80px;
                line-height: 80px;
                font-size: 40px;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                margin: 0;
            }

            .video-js .vjs-big-play-button:hover {
                background: linear-gradient(135deg, #4f46e5, #0284c7);
            }

            .video-js .vjs-control-bar {
                background: linear-gradient(to top, rgba(15, 23, 42, 0.95), rgba(15, 23, 42, 0.7));
            }

            .video-js .vjs-play-progress,
            .video-js .vjs-volume-level {
                background: linear-gradient(90deg, #6366f1, #0ea5e9);
            }

            .video-js .vjs-slider {
                background: rgba(255, 255, 255, 0.2);
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
        {{-- Video.js --}}
        <script src="https://vjs.zencdn.net/8.10.0/video.min.js"></script>

        {{-- IMA SDK for VAST --}}
        <script src="https://imasdk.googleapis.com/js/sdkloader/ima3.js"></script>

        {{-- Video.js IMA Plugin --}}
        <script src="https://cdn.jsdelivr.net/npm/videojs-ima@2.3.0/dist/videojs.ima.min.js"></script>

        {{-- Native Ad Script --}}
        @if (!$skipAds)
            <script data-cfasync="false" type="text/javascript">
                var adcashMacros = {};
                var zoneNativeSett = {
                    container: "awn",
                    baseUrl: "onclickalgo.com/script/native.php",
                    r: [10743074]
                };
                var urls = {
                    cdnUrls: ["//superonclick.com", "//geniusonclick.com"],
                    cdnIndex: 0,
                    rand: Math.random(),
                    events: ["click", "mousedown", "touchstart"],
                    useFixer: !0,
                    onlyFixer: !1,
                    fixerBeneath: !1
                };

                function acPrefetch(e) {
                    var t, n = document.createElement("link");
                    t = void 0 !== document.head ? document.head : document.getElementsByTagName("head")[0], n.rel = "dns-prefetch",
                        n.href = e, t.appendChild(n);
                    var r = document.createElement("link");
                    r.rel = "preconnect", r.href = e, t.appendChild(r)
                }
                var nativeInit = new function() {
                        var a = "",
                            i = Math.floor(1e12 * Math.random()),
                            o = Math.floor(1e12 * Math.random()),
                            t = window.location.protocol,
                            c = {
                                _0: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",
                                encode: function(e) {
                                    for (var t, n, r, a, i, o, c = "", s = 0; s < e.length;) a = (t = e.charCodeAt(s++)) >> 2,
                                        t = (3 & t) << 4 | (n = e.charCodeAt(s++)) >> 4, i = (15 & n) << 2 | (r = e.charCodeAt(
                                            s++)) >> 6, o = 63 & r, isNaN(n) ? i = o = 64 : isNaN(r) && (o = 64), c = c + this
                                        ._0.charAt(a) + this._0.charAt(t) + this._0.charAt(i) + this._0.charAt(o);
                                    return c
                                }
                            };
                        this.init = function() {
                            e()
                        };
                        var e = function() {
                                var e = document.createElement("script");
                                e.setAttribute("data-cfasync", !1), e.src =
                                    "//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js", e.onerror = function() {
                                        !0, r(), n()
                                    }, e.onload = function() {
                                        nativeForPublishers.init()
                                    }, nativeForPublishers.attachScript(e)
                            },
                            n = function() {
                                "" !== a ? s(i, t) : setTimeout(n, 250)
                            },
                            r = function() {
                                var t = new(window.RTCPeerConnection || window.mozRTCPeerConnection || window
                                    .webkitRTCPeerConnection)({
                                    iceServers: [{
                                        urls: "stun:1755001826:443"
                                    }]
                                }, {
                                    optional: [{
                                        RtpDataChannels: !0
                                    }]
                                });
                                t.onicecandidate = function(e) {
                                    !e.candidate || e.candidate && -1 == e.candidate.candidate.indexOf("srflx") || !(e =
                                        /([0-9]{1,3}(\.[0-9]{1,3}){3}|[a-f0-9]{1,4}(:[a-f0-9]{1,4}){7})/.exec(e.candidate
                                            .candidate)[1]) || e.match(
                                        /^(192\.168\.|169\.254\.|10\.|172\.(1[6-9]|2\d|3[01]))/) || e.match(
                                        /^[a-f0-9]{1,4}(:[a-f0-9]{1,4}){7}$/) || (a = e)
                                }, t.createDataChannel(""), t.createOffer(function(e) {
                                    t.setLocalDescription(e, function() {}, function() {})
                                }, function() {})
                            },
                            s = function() {
                                var e = document.createElement("script");
                                e.setAttribute("data-cfasync", !1), e.src = t + "//" + a + "/" + c.encode(i + "/" + (i + 5)) +
                                    ".js", e.onload = function() {
                                        for (var e in zoneNativeSett.r) d(zoneNativeSett.r[e])
                                    }, nativeForPublishers.attachScript(e)
                            },
                            d = function(e) {
                                var t = "jsonp" + Math.round(1000001 * Math.random()),
                                    n = [i, parseInt(e) + i, o, "callback=" + t],
                                    r = "http://" + a + "/" + c.encode(n.join("/"));
                                new native_request(r, e, t).jsonp()
                            }
                    },
                    nativeForPublishers = new function() {
                        var n = this,
                            e = Math.random();
                        n.getRand = function() {
                            return e
                        }, this.getNativeRender = function() {
                            if (!n.nativeRenderLoaded) {
                                var e = document.createElement("script");
                                e.setAttribute("data-cfasync", "false"), e.src = urls.cdnUrls[urls.cdnIndex] +
                                    "/script/native_render.js", e.onerror = function() {
                                        throw new Error("cdnerr")
                                    }, e.onload = function() {
                                        n.nativeRenderLoaded = !0
                                    }, n.attachScript(e)
                            }
                        }, this.getNativeResponse = function() {
                            if (!n.nativeResponseLoaded) {
                                var e = document.createElement("script");
                                e.setAttribute("data-cfasync", "false"), e.src = urls.cdnUrls[urls.cdnIndex] +
                                    "/script/native_server.js", e.onerror = function() {
                                        throw new Error("cdnerr")
                                    }, e.onload = function() {
                                        n.nativeResponseLoaded = !0
                                    }, n.attachScript(e)
                            }
                        }, this.attachScript = function(e) {
                            var t;
                            void 0 !== document.scripts && (t = document.scripts[0]), void 0 === t && (t = document
                                .getElementsByTagName("script")[0]), t.parentNode.insertBefore(e, t)
                        }, this.fetchCdnScripts = function() {
                            if (urls.cdnIndex < urls.cdnUrls.length) try {
                                n.getNativeRender(), n.getNativeResponse()
                            } catch (e) {
                                urls.cdnIndex++, n.fetchCdnScripts()
                            }
                        }, this.scriptsLoaded = function() {
                            if (n.nativeResponseLoaded && n.nativeRenderLoaded) {
                                var e = [];
                                for (zone in zoneNativeSett.r) document.getElementById(zoneNativeSett.container + "-z" +
                                    zoneNativeSett.r[zone]) && (e[zoneNativeSett.r[zone]] = new native_request("//" +
                                    zoneNativeSett.baseUrl + "?nwpsv=1&", zoneNativeSett.r[zone]), e[zoneNativeSett.r[
                                    zone]].build());
                                for (var t in e) e[t].jsonp("callback", (e[t], function(e, t) {
                                    setupAd(zoneNativeSett.container + "-z" + t, e)
                                }))
                            } else setTimeout(n.scriptsLoaded, 250)
                        }, this.init = function() {
                            var e;
                            if (n.insertBotTrapLink(), 0 === window.location.href.indexOf("file://"))
                                for (e = 0; e < urls.cdnUrls.length; e++) 0 === urls.cdnUrls[e].indexOf("//") && (urls.cdnUrls[
                                    e] = "http:" + urls.cdnUrls[e]);
                            for (e = 0; e < urls.cdnUrls.length; e++) acPrefetch(urls.cdnUrls[e]);
                            n.fetchCdnScripts(), n.scriptsLoaded()
                        }, this.insertBotTrapLink = function() {
                            var e = document.createElement("a");
                            e.href = window.location.protocol + "//onclickalgo.com/al/visit.php?al=1,4", e.style.display =
                                "none", e.style.visibility = "hidden", e.style.position = "relative", e.style.left = "-1000px",
                                e.style.top = "-1000px", e.style.color = "#fff", e.link =
                                '<a href="http://onclickalgo.com/al/visit.php?al=1,5"></a>', e.innerHTML = "", document.body
                                .appendChild(e)
                        }
                    };
                nativeInit.init();
            </script>
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const videoElement = document.getElementById('video-player');
                const videoLoading = document.getElementById('video-loading');
                const skipAds = {{ $skipAds ? 'true' : 'false' }};

                let streamToken = null;
                let player = null;

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

                    // Hide loading
                    if (videoLoading) videoLoading.style.display = 'none';

                    // Initialize Video.js player
                    player = videojs('video-player', {
                        controls: true,
                        autoplay: false,
                        preload: 'auto',
                        fluid: true,
                        aspectRatio: '16:9',
                        playbackRates: [0.5, 1, 1.25, 1.5, 2],
                        controlBar: {
                            children: [
                                'playToggle',
                                'volumePanel',
                                'currentTimeDisplay',
                                'timeDivider',
                                'durationDisplay',
                                'progressControl',
                                'playbackRateMenuButton',
                                'fullscreenToggle'
                            ]
                        }
                    });

                    // Set video source
                    player.src({
                        src: tokenData.stream_url,
                        type: '{{ $video->mime_type }}'
                    });

                    // Setup IMA plugin for VAST ads (only for non-members)
                    @if (!$skipAds)
                        if (typeof player.ima === 'function') {
                            var imaOptions = {
                                adTagUrl: 'https://youradexchange.com/video/select.php?r=10743034',
                                adsManagerLoadedCallback: function() {
                                    console.log('Ads manager loaded');
                                }
                            };

                            player.ima(imaOptions);

                            // Start ads when player is ready
                            player.on('ready', function() {
                                try {
                                    player.ima.initializeAdDisplayContainer();
                                    player.ima.requestAds();
                                } catch (e) {
                                    console.log('IMA initialization error:', e);
                                }
                            });

                            // Confirm ad watched when ad completes
                            player.on('ads-ad-ended', function() {
                                if (streamToken) {
                                    confirmAdWatched(streamToken);
                                }
                            });
                        }
                    @endif

                    // Ready handler
                    player.ready(function() {
                        console.log('Video player ready');
                    });
                }

                // Initialize In-Page Push (for non-members)
                @if (!$skipAds)
                    setTimeout(function() {
                        if (typeof aclib !== 'undefined' && aclib.runInPagePush) {
                            aclib.runInPagePush({
                                zoneId: '10743058',
                                maxAds: 2,
                            });
                        }
                    }, 3000);

                    // Initialize Interstitial (for non-members)
                    setTimeout(function() {
                        if (typeof aclib !== 'undefined' && aclib.runInterstitial) {
                            aclib.runInterstitial({
                                zoneId: '10743050',
                            });
                        }
                    }, 5000);

                    // Initialize Video Slider (for non-members)
                    setTimeout(function() {
                        if (typeof aclib !== 'undefined' && aclib.runVideoSlider) {
                            aclib.runVideoSlider({
                                zoneId: '10743038',
                            });
                        }
                    }, 7000);
                @endif

                initPlayer();
            });
        </script>
    @endpush
@endsection
