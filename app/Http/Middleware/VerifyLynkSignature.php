<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $token = $request->route('token');

        // Log for debugging
        Log::info('Lynk middleware - Token check', [
            'received_token' => $token,
            'expected_token' => $expectedToken ? substr($expectedToken, 0, 10) . '...' : 'NOT SET',
            'token_match' => $token === $expectedToken,
        ]);

        // Skip if token not configured (for testing)
        if (empty($expectedToken) || $expectedToken === 'your-lynk-signature-token') {
            Log::warning('Lynk middleware - Token not configured, allowing request');
            return $next($request);
        }

        // Check token from URL parameter
        if ($token !== $expectedToken) {
            Log::error('Lynk middleware - Invalid token', [
                'received' => $token,
                'expected' => substr($expectedToken, 0, 10) . '...',
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Invalid token',
            ], 401);
        }

        return $next($request);
    }
}

