<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AdblockMonetizationController extends Controller
{
    /**
     * Fetch adblock monetization content from adbpage.com
     * This is used when adblock is detected to still monetize the traffic
     */
    public function getAdblockContent()
    {
        try {
            // Cache the response for 5 minutes to reduce API calls
            $responseData = Cache::remember('adblock_monetization_content', 300, function () {
                $response = Http::timeout(10)->get('https://adbpage.com/adblock?v=3');

                if ($response->successful()) {
                    return $response->body();
                }

                return null;
            });

            if ($responseData) {
                return response($responseData)->header('Content-Type', 'text/html');
            }

            return response('', 204);
        } catch (\Exception $e) {
            // Log error but don't expose details to client
            \Log::error('Adblock monetization error: '.$e->getMessage());

            return response('', 204);
        }
    }

    /**
     * Check if adblock is detected (called via AJAX)
     * Returns the monetization script if adblock is active
     */
    public function checkAndServe(Request $request)
    {
        // This endpoint is called when JavaScript detects adblock
        try {
            $responseData = Cache::remember('adblock_monetization_content', 300, function () {
                $response = Http::timeout(10)->get('https://adbpage.com/adblock?v=3');

                if ($response->successful()) {
                    return $response->body();
                }

                return null;
            });

            return response()->json([
                'success' => true,
                'content' => $responseData,
            ]);
        } catch (\Exception $e) {
            \Log::error('Adblock monetization error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'content' => null,
            ]);
        }
    }
}
