<?php

namespace App\Http\Controllers;

use App\Services\MembershipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private MembershipService $membershipService
    ) {}

    /**
     * Handle Lynk payment webhook
     *
     * Expected payload structure:
     * {
     *   "event": "payment.received",
     *   "data": {
     *     "message_action": "SUCCESS",
     *     "message_data": {
     *       "customer": { "email": "...", "name": "...", "phone": "..." },
     *       "items": [{ "qty": 1, "title": "..." }]
     *     },
     *     "message_id": "..."
     *   }
     * }
     */
    public function handleLynkPayment(Request $request)
    {
        try {
            $payload = $request->all();

            // Log incoming webhook
            Log::info('Lynk webhook received', ['payload' => $payload]);

            // Validate event type
            $event = $payload['event'] ?? null;
            if ($event !== 'payment.received') {
                Log::info('Lynk webhook ignored: event type not payment.received', ['event' => $event]);

                return response()->json([
                    'success' => true,
                    'message' => 'Event ignored',
                ]);
            }

            // Validate payment status
            $messageAction = $payload['data']['message_action'] ?? null;
            if ($messageAction !== 'SUCCESS') {
                Log::info('Lynk webhook ignored: payment not successful', ['message_action' => $messageAction]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment not successful, ignored',
                ]);
            }

            // Extract required data
            $messageData = $payload['data']['message_data'] ?? [];
            $customer = $messageData['customer'] ?? [];
            $items = $messageData['items'] ?? [];
            $messageId = $payload['data']['message_id'] ?? null;

            $email = $customer['email'] ?? null;
            $name = $customer['name'] ?? null;
            $phone = $customer['phone'] ?? null;

            if (! $email) {
                Log::warning('Lynk webhook failed: missing customer email', ['payload' => $payload]);

                return response()->json([
                    'success' => false,
                    'message' => 'Customer email is required',
                ], 400);
            }

            // Get qty from first item (represents months of membership)
            $qty = 1; // Default to 1 month
            if (! empty($items) && isset($items[0]['qty'])) {
                $qty = (int) $items[0]['qty'];
            }

            // Ensure qty is at least 1
            $qty = max(1, $qty);

            // Activate membership
            $membership = $this->membershipService->activateMembershipFromWebhook(
                email: $email,
                name: $name,
                phone: $phone,
                months: $qty,
                paymentReference: $messageId
            );

            Log::info('Lynk webhook processed: membership activated', [
                'email' => $email,
                'months' => $qty,
                'membership_id' => $membership->id,
                'expires_at' => $membership->expires_at,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Membership activated successfully',
                'data' => [
                    'membership_id' => $membership->id,
                    'expires_at' => $membership->expires_at->toIso8601String(),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Lynk webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
            ], 500);
        }
    }
}
