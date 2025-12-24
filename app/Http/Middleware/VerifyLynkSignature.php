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
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-Lynk-Signature');
        $expectedToken = config('services.lynk.webhook_token');

        if (empty($expectedToken)) {
            // Log error: token not configured
            return response()->json([
                'success' => false,
                'message' => 'Webhook token not configured'
            ], 500);
        }

        if ($signature !== $expectedToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid signature'
            ], 401);
        }

        return $next($request);
    }
}
