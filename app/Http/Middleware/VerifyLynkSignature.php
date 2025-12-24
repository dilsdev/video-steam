<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyLynkSignature
{
    /**
     * Handle an incoming request.
     * Verifies the secret token from URL parameter.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedToken = config('services.lynk.webhook_token');

        // Skip if token not configured
        if (empty($expectedToken) || $expectedToken === 'your-lynk-signature-token') {
            return $next($request);
        }

        // Check token from URL parameter
        $token = $request->route('token');

        if ($token !== $expectedToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token',
            ], 401);
        }

        return $next($request);
    }
}
