@extends('layouts.app')

@section('title', $video->title)

@section('content')
    @push('styles')
        <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
    @endpush
    @push('scripts')
        <script src="https://cdn.plyr.io/3.7.8/plyr.js"></script>
    @endpush

    {{-- Modal 1 - Muncul di detik 2 --}}
    @if (!$skipAds)
        <div id="promo-modal-1"
            style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.85); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
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
                    style="display: inline-block; background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; padding: 1rem 3rem; border-radius: 12px; font-weight: 700; font-size: 1.1rem; text-decoration: none;">
                    Daftar Sekarang →
                </a>

                <p style="margin-top: 1rem; font-size: 0.85rem; color: #64748b; cursor: pointer;"
                    onclick="document.getElementById('promo-modal-1').style.display='none'">
                    Nanti saja
                </p>
            </div>
        </div>

        {{-- Modal 2 - Muncul di detik 15 --}}
        <div id="promo-modal-2"
            style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.85); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
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
                    <span style="color: #64748b;">di Situs ini</span>
                </p>

                <a href="{{ route('memberships.index') }}"
                    style="display: inline-block; background: linear-gradient(135deg, #6366f1, #4f46e5); color: white; padding: 1rem 3rem; border-radius: 12px; font-weight: 700; font-size: 1.1rem; text-decoration: none;">
                    Daftar Sekarang →
                </a>

                <p style="margin-top: 1rem; font-size: 0.85rem; color: #64748b; cursor: pointer;"
                    onclick="document.getElementById('promo-modal-2').style.display='none'">
                    Lanjutkan dengan iklan
                </p>
            </div>
        </div>
    @endif


    <div class="video-page" style="display: flex; flex-direction: column; gap: 2rem;">
        <div class="video-content-wrapper" style="display: grid; grid-template-columns: 1fr 180px; gap: 1.5rem;">
            <div class="video-main">
                {{-- Video Container --}}
                <div id="video-container"
                    style="position: relative; width: 100%; background: #000; border-radius: 16px; overflow: hidden;">
                    {{-- Loading State --}}
                    <div id="video-loading"
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; min-height: 400px; display: flex; align-items: center; justify-content: center; background: #0f172a; z-index: 10;">
                        <div style="text-align: center;">
                            <div
                                style="width: 50px; height: 50px; border: 3px solid rgba(99,102,241,0.3); border-top-color: #6366f1; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem;">
                            </div>
                            <p style="color: #64748b;">Memuat video...</p>
                        </div>
                    </div>

                    {{-- Plyr Video Player --}}
                    <video id="player" playsinline controls poster="{{ $video->getThumbnailUrl() }}">
                        <source src="" type="{{ $video->mime_type }}" />
                    </video>
                </div>

                {{-- Banner Ad 728x90 --}}
                @if (!$skipAds)
                    <div style="margin: 1.5rem 0; display: flex; justify-content: center;">
                        <div>
                            <script type="text/javascript">
                                aclib.runBanner({
                                    zoneId: '{{ config('adcash.banner_zone_id') }}'
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

                        <button id="share-btn" class="btn btn-secondary"
                            style="margin-left: auto; display: flex; align-items: center; gap: 0.5rem; background: var(--bg-hover); border: 1px solid var(--border);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                            </svg>
                            Copy Link Video
                        </button>

                        <script>
                            document.getElementById('share-btn').addEventListener('click', async function() {
                                const btn = this;
                                const originalHtml = btn.innerHTML;
                                const url = window.location.href;

                                function showSuccess() {
                                    btn.innerHTML = `
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="20 6 9 17 4 12"></polyline>
                                        </svg>
                                        <span style="color:var(--success)">Link Disalin!</span>
                                    `;
                                    setTimeout(() => {
                                        btn.innerHTML = originalHtml;
                                    }, 2000);
                                }

                                // Robust Copy Mechanism
                                if (navigator.clipboard && window.isSecureContext) {
                                    // Method 1: Modern API (HTTPS only)
                                    navigator.clipboard.writeText(url).then(showSuccess).catch(() => fallbackCopy(url));
                                } else {
                                    // Method 2: Fallback for HTTP/Older Browsers
                                    fallbackCopy(url);
                                }

                                function fallbackCopy(text) {
                                    const textArea = document.createElement("textarea");
                                    textArea.value = text;

                                    // Ensure textarea is not visible but part of DOM
                                    textArea.style.position = "fixed";
                                    textArea.style.left = "-9999px";
                                    textArea.style.top = "0";
                                    document.body.appendChild(textArea);

                                    textArea.focus();
                                    textArea.select();

                                    try {
                                        document.execCommand('copy');
                                        showSuccess();
                                    } catch (err) {
                                        console.error('Copy failed', err);
                                        alert('Gagal menyalin link. Silakan copy manual dari address bar.');
                                    }

                                    document.body.removeChild(textArea);
                                }
                            });
                        </script>
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
                        style="margin: 1.5rem 0; padding: 1rem; background: rgba(30,41,59,0.5); border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                            <span
                                style="font-size: 0.7rem; color: #64748b; text-transform: uppercase; letter-spacing: 1px;">Sponsored</span>
                        </div>
                        <div id="awn-z10743074"></div>
                    </div>

                    {{-- Adblock Monetization Container - Shows when adblock is detected --}}
                    <div id="adblock-monetization-container" style="display: none; margin: 1.5rem 0;"></div>
                @endif

                {{-- Member CTA Card --}}
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
                    <div>
                        <script type="text/javascript">
                            aclib.runBanner({
                                zoneId: '{{ config('adcash.sidebar_zone_id') }}'
                            });
                        </script>
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
                                    @if (auth()->check() && (auth()->user()->isAdmin() || auth()->id() === $related->user_id)) data-upload-url="{{ route('uploader.videos.auto-thumbnail', $related) }}" @endif
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

    @push('styles')
        <link rel="stylesheet" href="https://cdn.plyr.io/3.7.8/plyr.css" />
        <style>
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

            /* Modal Responsive */
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
                .related-videos-grid {
                    grid-template-columns: 1fr !important;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script id="aclib" type="text/javascript" src="//acscdn.com/script/aclib.js"></script>
        <script src="https://cdn.plyr.io/3.7.8/plyr.polyfilled.js"></script>

        {{-- Native Ad Script --}}
        @if (!$skipAds)
            <script data-cfasync="false" type="text/javascript">
                var adcashMacros = {};
                var zoneNativeSett = {
                    container: "awn",
                    baseUrl: "onclickalgo.com/script/native.php",
                    r: [{{ config('adcash.native_zone_id') }}]
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
            <a href="https://onclickalgo.com/al/visit.php?al=1,7"
                style="position:absolute;top:-1000px;left:-1000px;width:1px;height:1px;visibility:hidden;display:none;"></a>
            <noscript><a href="https://onclickalgo.com/al/visit.php?al=1,6"
                    style="position:absolute;top:-1000px;left:-1000px;width:1px;height:1px;visibility:hidden;display:none;"></a></noscript>
        @endif

        {{-- Adcash Ads --}}
        @if (!$skipAds)
            @php
                $adMode = config('adcash.mode', 'both');
                $showManualAds = in_array($adMode, ['manual', 'both']);
                $showAutoTag = in_array($adMode, ['auto', 'both']);
            @endphp

            {{-- Manual Ads: InPagePush, VideoSlider --}}
            @if ($showManualAds)
                <script type="text/javascript">
                    aclib.runInPagePush({
                        zoneId: '{{ config('adcash.inpage_push_zone_id') }}',
                        maxAds: 2
                    });
                </script>
                <script type="text/javascript">
                    aclib.runVideoSlider({
                        zoneId: '{{ config('adcash.video_slider_zone_id') }}'
                    });
                </script>
            @endif

            {{-- AutoTag: Automatic ad placement --}}
            @if ($showAutoTag)
                <script type="text/javascript">
                    aclib.runAutoTag({
                        zoneId: '{{ config('adcash.autotag_zone_id') }}'
                    });
                </script>
            @endif
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const videoElement = document.getElementById('player');
                const videoLoading = document.getElementById('video-loading');
                const promoModal1 = document.getElementById('promo-modal-1');
                const promoModal2 = document.getElementById('promo-modal-2');
                const skipAds = {{ $skipAds ? 'true' : 'false' }};

                // Modal handling
                @if (!$skipAds)
                    // Show Modal 1 after 2 seconds
                    setTimeout(function() {
                        if (promoModal1) promoModal1.style.display = 'flex';
                    }, 2000);

                    // Show Modal 2 after 15 seconds
                    setTimeout(function() {
                        if (promoModal2) promoModal2.style.display = 'flex';
                    }, 20000);

                    // Close button handlers
                    document.querySelectorAll('.close-modal').forEach(function(btn) {
                        btn.addEventListener('click', function() {
                            const modalId = this.getAttribute('data-modal');
                            const modal = document.getElementById(modalId);
                            if (modal) modal.style.display = 'none';
                        });
                    });

                    // Click outside to close
                    [promoModal1, promoModal2].forEach(function(modal) {
                        if (modal) {
                            modal.addEventListener('click', function(e) {
                                if (e.target === modal) modal.style.display = 'none';
                            });
                        }
                    });
                @endif


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
                    if (!videoElement) {
                        console.error('Video element not found');
                        return;
                    }

                    const tokenData = await getStreamToken();

                    if (!tokenData || !tokenData.token) {
                        console.error('Failed to get token:', tokenData);
                        if (videoLoading) {
                            videoLoading.innerHTML =
                                '<div style="text-align:center;color:#ef4444;"><p>Gagal memuat video</p><button onclick="location.reload()" style="margin-top:1rem;padding:0.5rem 1rem;background:#6366f1;color:white;border:none;border-radius:8px;cursor:pointer;">Refresh</button></div>';
                        }
                        return;
                    }

                    // Set video source
                    const source = videoElement.querySelector('source');
                    if (source) {
                        source.src = tokenData.stream_url;
                        videoElement.load();
                    }

                    // Initialize Plyr
                    try {
                        const player = new Plyr('#player', {
                            controls: ['play-large', 'play', 'progress', 'current-time', 'duration', 'mute',
                                'volume', 'captions', 'settings', 'pip', 'airplay', 'fullscreen'
                            ],
                            settings: ['captions', 'quality', 'speed', 'loop'],
                            speed: {
                                selected: 1,
                                options: [0.5, 0.75, 1, 1.25, 1.5, 1.75, 2]
                            },
                            keyboard: {
                                focused: true,
                                global: true
                            },
                            tooltips: {
                                controls: true,
                                seek: true
                            },
                            displayDuration: true,
                            invertTime: false,
                            ratio: '16:9'
                        });

                        player.on('ready', function() {
                            if (videoLoading) videoLoading.style.display = 'none';
                        });

                        player.on('play', function() {
                            if (!skipAds && tokenData.token) {
                                confirmAdWatched(tokenData.token);
                            }
                        });

                        player.on('error', function(error) {
                            console.error('Player error:', error);
                            if (videoLoading) {
                                videoLoading.style.display = 'flex';
                                videoLoading.innerHTML =
                                    '<div style="text-align:center;color:#ef4444;"><p>Error pemutaran video</p></div>';
                            }
                        });
                    } catch (e) {
                        console.error('Plyr initialization failed:', e);
                        // Fallback: just show the native video if Plyr fails
                        videoElement.controls = true;
                        if (videoLoading) videoLoading.style.display = 'none';
                    }
                }

                initPlayer();
            });
        </script>

        {{-- Adblock Detection & Alternative Monetization --}}
        @if (!$skipAds)
            <script>
                (function() {
                    // Adblock detection
                    function detectAdblock() {
                        return new Promise((resolve) => {
                            // Method 1: Check if ad script loaded
                            if (typeof aclib === 'undefined') {
                                resolve(true);
                                return;
                            }

                            // Method 2: Create a bait element
                            const bait = document.createElement('div');
                            bait.innerHTML = '&nbsp;';
                            bait.className =
                                'adsbox ad-zone pub_300x250 pub_300x250m pub_728x90 text-ad textAd text_ad text_ads text-ads text-ad-links ad-text adSense adBlock adContent adBanner';
                            bait.style.cssText =
                                'width: 1px !important; height: 1px !important; position: absolute !important; left: -10000px !important; top: -1000px !important;';
                            document.body.appendChild(bait);

                            // Wait a bit for adblocker to act
                            setTimeout(() => {
                                const isBlocked = bait.offsetHeight === 0 ||
                                    bait.offsetWidth === 0 ||
                                    bait.offsetParent === null ||
                                    window.getComputedStyle(bait).display === 'none' ||
                                    window.getComputedStyle(bait).visibility === 'hidden';
                                bait.remove();
                                resolve(isBlocked);
                            }, 100);
                        });
                    }

                    // Load adblock monetization content
                    async function loadAdblockMonetization() {
                        try {
                            const response = await fetch('{{ route('adblock.check') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': window.csrfToken
                                }
                            });

                            const data = await response.json();

                            if (data.success && data.content) {
                                const container = document.getElementById('adblock-monetization-container');
                                if (container) {
                                    container.innerHTML = data.content;
                                    container.style.display = 'block';

                                    // Execute any scripts in the content
                                    const scripts = container.querySelectorAll('script');
                                    scripts.forEach(script => {
                                        const newScript = document.createElement('script');
                                        if (script.src) {
                                            newScript.src = script.src;
                                        } else {
                                            newScript.textContent = script.textContent;
                                        }
                                        document.head.appendChild(newScript);
                                    });
                                }
                            }
                        } catch (error) {
                            console.log('Alternative monetization not available');
                        }
                    }

                    // Run detection after page load
                    window.addEventListener('load', async () => {
                        // Small delay to ensure ad scripts have had time to load/block
                        setTimeout(async () => {
                            const adblockDetected = await detectAdblock();

                            if (adblockDetected) {
                                console.log('Adblock detected, loading alternative monetization');
                                loadAdblockMonetization();
                            }
                        }, 1500);
                    });
                })();
            </script>
        @endif
    @endpush
@endsection
