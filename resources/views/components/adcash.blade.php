@props(['type'])

@if(config('adcash.enabled'))
    @php
        $zoneId = config("adcash.zones.{$type}");
    @endphp

    @if($zoneId)
        <!-- AdCash {{ $type }} Start -->
        @switch($type)
            @case('autotag')
                <script id="aclib" type="text/javascript" src="//acscdn.com/script/aclib.js"></script>
                <script type="text/javascript">
                    aclib.runAutoTag({
                        zoneId: '{{ $zoneId }}',
                    });
                </script>
                @break

            @case('pop_under')
                <script id="aclib" type="text/javascript" src="//acscdn.com/script/aclib.js"></script>
                <script type="text/javascript">
                    aclib.runPop({
                        zoneId: '{{ $zoneId }}',
                    });
                </script>
                @break

            @case('interstitial')
                <script id="aclib" type="text/javascript" src="//acscdn.com/script/aclib.js"></script>
                <script type="text/javascript">
                    aclib.runInterstitial({
                        zoneId: '{{ $zoneId }}',
                    });
                </script>
                @break
            
            @case('in_page_push')
                <script id="aclib" type="text/javascript" src="//acscdn.com/script/aclib.js"></script>
                <script type="text/javascript">
                    aclib.runInPagePush({
                        zoneId: '{{ $zoneId }}',
                    });
                </script>
                @break

            @case('video_slider')
                 <!-- Video Slider biasanya script khusus, kita pakai generic banner script jika tidak ada detail, tapi untuk Video Slider biasanya float -->
                 <script type="text/javascript">
                     var adcash = adcash || {};
                     adcash.adId = '{{ $zoneId }}';
                     adcash.adWidth = 0; // Responsive/Slider
                     adcash.adHeight = 0;
                     (function() {
                         var ac = document.createElement("script");
                         ac.type = "text/javascript";
                         ac.async = true;
                         ac.src = "//acscdn.com/script/aclib.js"; 
                         var s = document.getElementsByTagName("script")[0];
                         s.parentNode.insertBefore(ac, s);
                     })();
                 </script>
                 @break

            @case('banner_728x90')
                 <div style="text-align:center; margin: 20px 0;">
                    <script type="text/javascript">
                        var adcash = adcash || {};
                        adcash.adId = '{{ $zoneId }}';
                        adcash.adWidth = 728;
                        adcash.adHeight = 90;
                    </script>
                    <script type="text/javascript" src="//acscdn.com/script/aclib.js"></script>
                 </div>
                 @break

            @case('banner_160x600')
                 <div style="text-align:center; margin: 20px 0;">
                    <script type="text/javascript">
                        var adcash = adcash || {};
                        adcash.adId = '{{ $zoneId }}';
                        adcash.adWidth = 160;
                        adcash.adHeight = 600;
                    </script>
                    <script type="text/javascript" src="//acscdn.com/script/aclib.js"></script>
                 </div>
                 @break

            @case('native')
                <div id="ac_native_{{ $zoneId }}"></div>
                <script type="text/javascript">
                    var adcash = adcash || {};
                    adcash.adId = '{{ $zoneId }}';
                    adcash.adWidth = 0; // Native
                    adcash.adHeight = 0;
                </script>
                <script type="text/javascript" src="//acscdn.com/script/aclib.js"></script>
                @break

            @default
                <!-- Unknown AdCash Type: {{ $type }} -->
        @endswitch
        <!-- AdCash {{ $type }} End -->
    @endif
@endif
