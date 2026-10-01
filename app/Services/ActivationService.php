<?php

namespace App\Services;

use App\Events\UserActivated;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\User;

class ActivationService
{
    public function __construct(private readonly PaymentGatewayService $gateway) {}

    public function requiredReferrals(): int
    {
        return (int) Setting::get('required_referrals', 2);
    }

    public function activationFee(): int
    {
        return (int) Setting::get('activation_fee', 199);
    }

    public function paidReferralsCount(User $user): int
    {
        return $user->referralsMade()->where('referred_paid', true)->count();
    }

    /**
     * Server-computed, idempotent activation check. Never trust client-reported status.
     */
    public function checkAndActivate(User $user): bool
    {
        if ($user->status === 'active') {
            return false;
        }

        if (! $user->has_paid) {
            return false;
        }

        if ($this->paidReferralsCount($user) < $this->requiredReferrals()) {
            return false;
        }

        $user->status = 'active';
        $user->save();

        event(new UserActivated($user));

        return true;
    }

    /**
     * Creates a pending Payment row and a matching gateway order for a
     * user's self-activation fee. Shared by the mobile API and the website
     * so both go through the exact same webhook-confirmed flow. Caller is
     * responsible for checking $user->has_paid first.
     *
     * @return array{payment_id: int, gateway: string, order_id: string, amount: int, currency: string, redirect_url: string}
     */
    public function initiatePayment(User $user): array
    {
        $amount = $this->activationFee();
        $order = $this->gateway->createOrder($amount, receipt: "activation-{$user->id}-".now()->timestamp);

        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => $amount,
            'gateway' => 'phonepe',
            'gateway_txn_id' => $order['id'],
            'status' => 'pending',
            'purpose' => 'self_activation',
        ]);

        return [
            'payment_id' => $payment->id,
            'gateway' => 'phonepe',
            'order_id' => $order['id'],
            'amount' => $order['amount'],
            'currency' => $order['currency'],
            'redirect_url' => $order['redirect_url'],
        ];
    }
}
