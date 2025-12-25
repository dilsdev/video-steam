<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Nginx X-Accel-Redirect
    |--------------------------------------------------------------------------
    |
    | Enable Nginx X-Accel-Redirect for production. When enabled, Laravel will
    | send X-Accel-Redirect header instead of streaming the file directly.
    |
    */
    'use_nginx' => env('STREAMING_USE_NGINX', false),

    /*
    |--------------------------------------------------------------------------
    | Preview Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for video preview generation using FFmpeg.
    |
    */
    'preview_duration' => env('PREVIEW_DURATION', 10), // seconds
    'preview_quality' => env('PREVIEW_QUALITY', 23), // CRF value (lower = better quality)
    'thumbnail_time' => env('THUMBNAIL_TIME', 10), // Extract frame at this second

    /*
    |--------------------------------------------------------------------------
    | FFmpeg Configuration
    |--------------------------------------------------------------------------
    |
    | Path to FFmpeg binary. On Windows, make sure FFmpeg is in PATH or
    | specify the full path here.
    |
    */
    'ffmpeg_path' => env('FFMPEG_PATH', 'ffmpeg'),
    'ffprobe_path' => env('FFPROBE_PATH', 'ffprobe'),

    /*
    |--------------------------------------------------------------------------
    | Nginx Internal Paths
    |--------------------------------------------------------------------------
    |
    | These paths are used for X-Accel-Redirect. They should match the
    | internal locations configured in Nginx.
    |
    */
    'nginx_videos_path' => '/internal-videos/',
    'nginx_previews_path' => '/internal-previews/',
];
