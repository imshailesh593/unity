<?php

use App\Models\Referral;
use App\Models\Setting;
use App\Models\User;
use App\Services\PaymentGatewayService;

beforeEach(function () {
    Setting::set('activation_fee', 199);
    Setting::set('required_referrals', 2);
});

it('rejects unauthenticated requests to protected endpoints', function () {
    $this->getJson('/api/v1/user/me')->assertUnauthorized();
    $this->getJson('/api/v1/user/referral')->assertUnauthorized();
    $this->postJson('/api/v1/payment/initiate')->assertUnauthorized();
});

it('returns the authenticated user profile with activation progress', function () {
    $user = User::factory()->create(['has_paid' => true]);

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/user/me')
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.activation_progress.has_paid', true)
        ->assertJsonPath('data.activation_progress.referrals_required', 2);
});

it('returns referral summary with referred users', function () {
    $user = User::factory()->create();
    $referred = User::factory()->create(['referred_by' => $user->id]);
    Referral::create(['referrer_id' => $user->id, 'referred_id' => $referred->id, 'referred_paid' => true]);

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/v1/user/referral');

    $response->assertOk()
        ->assertJsonPath('referral_code', $user->referral_code)
        ->assertJsonPath('referred_count', 1)
        ->assertJsonPath('referrals_paid', 1);
});

it('initiates a payment order for an unpaid user', function () {
    $this->mock(PaymentGatewayService::class, function ($mock) {
        $mock->shouldReceive('createOrder')->andReturn(['id' => 'order_abc', 'amount' => 199, 'currency' => 'INR']);
    });

    $user = User::factory()->create(['has_paid' => false]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/payment/initiate')
        ->assertCreated()
        ->assertJsonPath('order_id', 'order_abc');

    $this->assertDatabaseHas('payments', [
        'user_id' => $user->id,
        'gateway_txn_id' => 'order_abc',
        'status' => 'pending',
    ]);
});

it('refuses to initiate a payment for an already-paid user', function () {
    $user = User::factory()->create(['has_paid' => true]);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/payment/initiate')
        ->assertStatus(409);
});

it('registers a device token for the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/device-token', ['token' => 'fcm-token-1', 'platform' => 'android'])
        ->assertCreated();

    $this->assertDatabaseHas('device_tokens', ['user_id' => $user->id, 'token' => 'fcm-token-1']);
});
