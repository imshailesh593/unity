<?php

namespace App\Services;

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class PaymentGatewayService
{
    private Api $api;

    public function __construct()
    {
        $this->api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
    }

    /**
     * @return array{id: string, amount: int, currency: string}
     */
    public function createOrder(int $amountInRupees, string $receipt): array
    {
        $order = $this->api->order->create([
            'amount' => $amountInRupees * 100,
            'currency' => 'INR',
            'receipt' => $receipt,
        ]);

        return [
            'id' => $order['id'],
            'amount' => $amountInRupees,
            'currency' => 'INR',
        ];
    }

    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        try {
            $this->api->utility->verifyWebhookSignature(
                $payload,
                $signature,
                config('services.razorpay.webhook_secret')
            );

            return true;
        } catch (SignatureVerificationError) {
            return false;
        }
    }
}
