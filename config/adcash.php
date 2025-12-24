<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Adcash Configuration
    |--------------------------------------------------------------------------
    |
    | Configure all Adcash advertisement settings here. These values are
    | read from your .env file and can be easily changed without modifying
    | any blade templates or code.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Ads Mode
    |--------------------------------------------------------------------------
    |
    | Options:
    | - 'auto'   : Only use AutoTag (automatic ad placement)
    | - 'manual' : Only use manual ads (InPagePush, Interstitial, VideoSlider, Banner, Native)
    | - 'both'   : Use both AutoTag and manual ads
    |
    */
    'mode' => env('ADCASH_MODE', 'both'),

    /*
    |--------------------------------------------------------------------------
    | Zone IDs
    |--------------------------------------------------------------------------
    */

    // Manual Ads Zone IDs
    'banner_zone_id' => env('ADCASH_BANNER_ZONE_ID', '10743066'),
    'sidebar_zone_id' => env('ADCASH_SIDEBAR_ZONE_ID', '10743082'),
    'native_zone_id' => env('ADCASH_NATIVE_ZONE_ID', '10743074'),
    'inpage_push_zone_id' => env('ADCASH_INPAGE_PUSH_ZONE_ID', '10743058'),
    'interstitial_zone_id' => env('ADCASH_INTERSTITIAL_ZONE_ID', '10743050'),
    'video_slider_zone_id' => env('ADCASH_VIDEO_SLIDER_ZONE_ID', '10743038'),

    // AutoTag Zone ID
    'autotag_zone_id' => env('ADCASH_AUTOTAG_ZONE_ID', 'o1bir8ndhl'),
];
