<?php

use App\Models\Payment;
use App\Models\Referral;
use App\Models\Setting;
use App\Models\User;
use App\Services\PaymentGatewayService;

beforeEach(function () {
    Setting::set('activation_fee', 199);
    Setting::set('required_referrals', 2);
});

function fakeValidSignature(): void
{
    test()->mock(PaymentGatewayService::class, function ($mock) {
        $mock->shouldReceive('verifyWebhookSignature')->andReturn(true);
    });
}

function razorpayPayload(string $orderId, string $event = 'payment.captured'): array
{
    return [
        'event' => $event,
        'payload' => [
            'payment' => [
                'entity' => [
                    'order_id' => $orderId,
                    'id' => 'pay_'.$orderId,
                ],
            ],
        ],
    ];
}

it('rejects a webhook with an invalid signature', function () {
    test()->mock(PaymentGatewayService::class, function ($mock) {
        $mock->shouldReceive('verifyWebhookSignature')->andReturn(false);
    });

    $this->postJson('/api/v1/webhooks/payment', razorpayPayload('order_x'), [
        'X-Razorpay-Signature' => 'bad-signature',
    ])->assertStatus(400);
});

it('marks payment successful and activates a user once both conditions are met', function () {
    fakeValidSignature();

    $referrer = User::factory()->create(['has_paid' => true, 'status' => 'pending']);
    Referral::create(['referrer_id' => $referrer->id, 'referred_id' => User::factory()->create()->id, 'referred_paid' => true]);

    $referredUser = User::factory()->create(['referred_by' => $referrer->id]);
    Referral::create(['referrer_id' => $referrer->id, 'referred_id' => $referredUser->id, 'referred_paid' => false]);

    $payment = Payment::create([
        'user_id' => $referredUser->id,
        'amount' => 199,
        'gateway' => 'razorpay',
        'gateway_txn_id' => 'order_referred',
        'status' => 'pending',
        'purpose' => 'self_activation',
    ]);

    $this->postJson('/api/v1/webhooks/payment', razorpayPayload('order_referred'), [
        'X-Razorpay-Signature' => 'sig',
    ])->assertOk();

    expect($payment->fresh()->status)->toBe('success');
    expect($referredUser->fresh()->has_paid)->toBeTrue();

    // The referrer now has 2 paid referrals + has already paid -> should activate.
    expect($referrer->fresh()->status)->toBe('active');
});

it('is idempotent when the same webhook event is replayed', function () {
    fakeValidSignature();

    $user = User::factory()->create();
    $payment = Payment::create([
        'user_id' => $user->id,
        'amount' => 199,
        'gateway' => 'razorpay',
        'gateway_txn_id' => 'order_dup',
        'status' => 'success',
        'purpose' => 'self_activation',
    ]);

    $response = $this->postJson('/api/v1/webhooks/payment', razorpayPayload('order_dup'), [
        'X-Razorpay-Signature' => 'sig',
    ]);

    $response->assertOk()->assertJson(['message' => 'Already processed.']);
});

it('returns 404 for an unknown order', function () {
    fakeValidSignature();

    $this->postJson('/api/v1/webhooks/payment', razorpayPayload('does_not_exist'), [
        'X-Razorpay-Signature' => 'sig',
    ])->assertStatus(404);
});

it('increments cause raised_amount on a successful contribution, without touching activation', function () {
    fakeValidSignature();

    $organizer = User::factory()->organizer()->create();
    $cause = \App\Models\Cause::factory()->create([
        'organizer_id' => $organizer->id,
        'status' => 'published',
        'goal_amount' => 100000,
        'raised_amount' => 5000,
    ]);

    $contributor = User::factory()->create(['has_paid' => false, 'status' => 'registered']);
    $payment = Payment::create([
        'user_id' => $contributor->id,
        'cause_id' => $cause->id,
        'amount' => 500,
        'gateway' => 'razorpay',
        'gateway_txn_id' => 'order_cause_contrib',
        'status' => 'pending',
        'purpose' => 'cause_contribution',
    ]);

    $this->postJson('/api/v1/webhooks/payment', razorpayPayload('order_cause_contrib'), [
        'X-Razorpay-Signature' => 'sig',
    ])->assertOk();

    expect($payment->fresh()->status)->toBe('success');
    expect($cause->fresh()->raised_amount)->toBe(5500);
    // Contribution must never affect the contributor's own activation state.
    expect($contributor->fresh()->has_paid)->toBeFalse();
});
