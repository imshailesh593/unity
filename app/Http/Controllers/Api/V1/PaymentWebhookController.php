<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\PaymentSucceeded;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Referral;
use App\Services\ActivationService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentGatewayService $gateway,
        private readonly ActivationService $activation,
    ) {}

    /**
     * PhonePe webhook callback. Basic-Auth verified via the PhonePe SDK,
     * idempotent (safe to receive the same event more than once), and never
     * trusts the payload for anything beyond "which order does this refer
     * to" — the callback's own verified state is what we act on.
     */
    public function handle(Request $request)
    {
        $callback = $this->gateway->verifyCallback(
            ['authorization' => $request->header('Authorization', '')],
            $request->getContent(),
        );

        if (! $callback) {
            Log::warning('Payment webhook signature verification failed.');

            return response()->json(['message' => 'Invalid signature.'], 400);
        }

        $orderId = $callback->getPayload()->getMerchantOrderId();

        if (! $orderId) {
            return response()->json(['message' => 'No order reference in payload.'], 422);
        }

        $payment = Payment::query()->where('gateway_txn_id', $orderId)->first();

        if (! $payment) {
            return response()->json(['message' => 'Unknown order.'], 404);
        }

        if ($payment->status === 'success') {
            return response()->json(['message' => 'Already processed.']);
        }

        if ($callback->getType() !== 'CHECKOUT_ORDER_COMPLETED') {
            $payment->update(['status' => 'failed']);

            return response()->json(['message' => 'Acknowledged.']);
        }

        DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'success']);

            $user = $payment->user;
            $user->update(['has_paid' => true]);

            $this->activation->checkAndActivate($user);

            if ($user->referred_by) {
                Referral::query()
                    ->where('referrer_id', $user->referred_by)
                    ->where('referred_id', $user->id)
                    ->update(['referred_paid' => true]);

                if ($referrer = $user->referrer) {
                    $this->activation->checkAndActivate($referrer);
                }
            }
        });

        event(new PaymentSucceeded($payment));

        return response()->json(['message' => 'Processed.']);
    }
}
