<?php

return [
    'enabled' => env('ADCASH_ENABLED', true),
    
    'zones' => [
        'video_slider' => env('ADCASH_VIDEO_SLIDER', '10745238'), // Anti-AdBlock
        'interstitial' => env('ADCASH_INTERSTITIAL', '10745230'),
        'in_page_push' => env('ADCASH_IN_PAGE_PUSH', '10745222'),
        'native'       => env('ADCASH_NATIVE', '10745214'),
        'banner_160x600' => env('ADCASH_BANNER_160X600', '10745206'),
        'banner_728x90'  => env('ADCASH_BANNER_728X90', '10745198'),
        'pop_under'    => env('ADCASH_POP_UNDER', '10745186'), // Anti-AdBlock
        'autotag'      => env('ADCASH_AUTOTAG', 'igfjjlmgoi'), // Anti-AdBlock
    ],

    // Script base URL (gunakan versi Anti-AdBlock jika tersedia)
    'script_url' => '//acscdn.com/script/aclib.js', // Standard/Anti-AdBlock library
];