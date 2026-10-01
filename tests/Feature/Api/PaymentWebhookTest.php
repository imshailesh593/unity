<?php

use App\Models\Payment;
use App\Models\Referral;
use App\Models\Setting;
use App\Models\User;
use App\Services\PaymentGatewayService;
use PhonePe\payments\v2\models\response\CallbackResponse;

beforeEach(function () {
    Setting::set('activation_fee', 199);
    Setting::set('required_referrals', 2);
});

function phonePeCallback(string $merchantOrderId, string $type = 'CHECKOUT_ORDER_COMPLETED'): CallbackResponse
{
    return CallbackResponse::getInstance(json_encode([
        'type' => $type,
        'payload' => [
            'merchantId' => 'TESTMERCHANT',
            'merchantOrderId' => $merchantOrderId,
            'orderId' => 'OMO'.strtoupper($merchantOrderId),
            'state' => $type === 'CHECKOUT_ORDER_COMPLETED' ? 'COMPLETED' : 'FAILED',
            'amount' => 19900,
        ],
    ]));
}

function fakeValidCallback(string $merchantOrderId, string $type = 'CHECKOUT_ORDER_COMPLETED'): void
{
    test()->mock(PaymentGatewayService::class, function ($mock) use ($merchantOrderId, $type) {
        $mock->shouldReceive('verifyCallback')->andReturn(phonePeCallback($merchantOrderId, $type));
    });
}

it('rejects a webhook with an invalid signature', function () {
    test()->mock(PaymentGatewayService::class, function ($mock) {
        $mock->shouldReceive('verifyCallback')->andReturn(null);
    });

    $this->postJson('/api/v1/webhooks/payment', [], [
        'Authorization' => 'bad-signature',
    ])->assertStatus(400);
});

it('marks payment successful and activates a user once both conditions are met', function () {
    fakeValidCallback('order_referred');

    $referrer = User::factory()->create(['has_paid' => true, 'status' => 'pending']);
    Referral::create(['referrer_id' => $referrer->id, 'referred_id' => User::factory()->create()->id, 'referred_paid' => true]);

    $referredUser = User::factory()->create(['referred_by' => $referrer->id]);
    Referral::create(['referrer_id' => $referrer->id, 'referred_id' => $referredUser->id, 'referred_paid' => false]);

    $payment = Payment::create([
        'user_id' => $referredUser->id,
        'amount' => 199,
        'gateway' => 'phonepe',
        'gateway_txn_id' => 'order_referred',
        'status' => 'pending',
        'purpose' => 'self_activation',
    ]);

    $this->postJson('/api/v1/webhooks/payment', [], [
        'Authorization' => 'sig',
    ])->assertOk();

    expect($payment->fresh()->status)->toBe('success');
    expect($referredUser->fresh()->has_paid)->toBeTrue();

    // The referrer now has 2 paid referrals + has already paid -> should activate.
    expect($referrer->fresh()->status)->toBe('active');
});

it('is idempotent when the same webhook event is replayed', function () {
    fakeValidCallback('order_dup');

    $user = User::factory()->create();
    $payment = Payment::create([
        'user_id' => $user->id,
        'amount' => 199,
        'gateway' => 'phonepe',
        'gateway_txn_id' => 'order_dup',
        'status' => 'success',
        'purpose' => 'self_activation',
    ]);

    $response = $this->postJson('/api/v1/webhooks/payment', [], [
        'Authorization' => 'sig',
    ]);

    $response->assertOk()->assertJson(['message' => 'Already processed.']);
});

it('returns 404 for an unknown order', function () {
    fakeValidCallback('does_not_exist');

    $this->postJson('/api/v1/webhooks/payment', [], [
        'Authorization' => 'sig',
    ])->assertStatus(404);
});

it('marks the payment failed on a CHECKOUT_ORDER_FAILED callback without activating the user', function () {
    fakeValidCallback('order_failed', 'CHECKOUT_ORDER_FAILED');

    $user = User::factory()->create(['has_paid' => false]);
    $payment = Payment::create([
        'user_id' => $user->id,
        'amount' => 199,
        'gateway' => 'phonepe',
        'gateway_txn_id' => 'order_failed',
        'status' => 'pending',
        'purpose' => 'self_activation',
    ]);

    $this->postJson('/api/v1/webhooks/payment', [], [
        'Authorization' => 'sig',
    ])->assertOk();

    expect($payment->fresh()->status)->toBe('failed');
    expect($user->fresh()->has_paid)->toBeFalse();
});
