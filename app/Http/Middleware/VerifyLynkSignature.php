<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyLynkSignature
{
    /**
     * Handle an incoming request.
     * For now, allow all requests without signature verification.
     * TODO: Implement proper signature verification based on Lynk documentation.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip signature verification - Lynk webhook doesn't require it
        return $next($request);
    }
}
