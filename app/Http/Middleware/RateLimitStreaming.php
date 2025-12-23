<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\SecurityLog;
use Symfony\Component\HttpFoundation\Response;

class RateLimitStreaming
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $key = 'stream_limit:' . $ip;
        $limit = 100; // max 100 request per menit
        
        $current = Cache::get($key, 0);
        
        if ($current >= $limit) {
            SecurityLog::log(
                'rate_limit_exceeded',
                $ip,
                auth()->id(),
                'Streaming rate limit exceeded'
            );
            
            abort(429, 'Terlalu banyak permintaan. Silakan tunggu.');
        }
        
        Cache::put($key, $current + 1, 60);
        
        return $next($request);
    }
}
