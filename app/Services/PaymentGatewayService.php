<?php

namespace App\Services;

use App\Models\PaymentGatewaySetting;
use PhonePe\common\exceptions\PhonePeException;
use PhonePe\payments\v2\models\request\builders\StandardCheckoutPayRequestBuilder;
use PhonePe\payments\v2\models\response\CallbackResponse;
use PhonePe\payments\v2\standardCheckout\StandardCheckoutClient;

class PaymentGatewayService
{
    private PaymentGatewaySetting $settings;

    public function __construct()
    {
        $this->settings = PaymentGatewaySetting::forGateway('phonepe');
    }

    private function client(): StandardCheckoutClient
    {
        return StandardCheckoutClient::getInstance(
            $this->settings->activeClientId() ?? config('services.phonepe.client_id'),
            $this->settings->activeClientVersion() ?? config('services.phonepe.client_version'),
            $this->settings->activeClientSecret() ?? config('services.phonepe.client_secret'),
            $this->settings->activeEnv(),
        );
    }

    /**
     * Creates a PhonePe order and returns the URL the user's browser must be
     * redirected to in order to complete payment on PhonePe's hosted page.
     *
     * @return array{id: string, amount: int, currency: string, redirect_url: string}
     */
    public function createOrder(int $amountInRupees, string $receipt): array
    {
        $payRequest = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($receipt)
            ->amount($amountInRupees * 100)
            ->message("Unity activation — {$receipt}")
            ->redirectUrl(route('payment.phonepe.return'))
            ->build();

        $response = $this->client()->pay($payRequest);

        return [
            'id' => $receipt,
            'amount' => $amountInRupees,
            'currency' => 'INR',
            'redirect_url' => $response->getRedirectUrl(),
        ];
    }

    public function checkOrderStatus(string $merchantOrderId): string
    {
        return $this->client()->getOrderStatus($merchantOrderId)->getState();
    }

    /**
     * Verifies a PhonePe webhook callback and returns the decoded response,
     * or null if the callback's authorization header doesn't match our
     * configured webhook credentials.
     */
    public function verifyCallback(array $headers, string $rawBody): ?CallbackResponse
    {
        try {
            return $this->client()->verifyCallbackResponse(
                $headers,
                $rawBody,
                $this->settings->webhook_username,
                $this->settings->webhook_password,
            );
        } catch (PhonePeException) {
            return null;
        }
    }
}
