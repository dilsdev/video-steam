<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyLynkSignature
{
    /**
     * Handle an incoming request.
     * Validates the X-Lynk-Signature header against the configured token.
     * If token is not configured, allow all requests (for development).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('services.lynk.webhook_token');

        // Skip verification if token not configured (development mode)
        if (empty($expectedToken) || $expectedToken === 'your-lynk-signature-token') {
            return $next($request);
        }

        $signature = $request->header('X-Lynk-Signature');

        if ($signature !== $expectedToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 401);
        }

        return $next($request);
    }
}
