<?php

use App\Models\Referral;
use App\Models\User;
use App\Services\FirebaseAuthService;

function fakeFirebaseIdentity(string $uid = 'firebase-uid-1', string $phone = '+919000000001'): void
{
    test()->mock(FirebaseAuthService::class, function ($mock) use ($uid, $phone) {
        $mock->shouldReceive('verify')->andReturn(['uid' => $uid, 'phone' => $phone]);
    });
}

it('tells the client registration is required for an unknown firebase uid', function () {
    fakeFirebaseIdentity();

    $response = $this->postJson('/api/v1/auth/verify-otp', ['id_token' => 'token']);

    $response->assertOk()->assertJson(['status' => 'registration_required']);
});

it('logs in an existing user on verify-otp', function () {
    $user = User::factory()->create(['firebase_uid' => 'firebase-uid-1']);
    fakeFirebaseIdentity(uid: 'firebase-uid-1');

    $response = $this->postJson('/api/v1/auth/verify-otp', ['id_token' => 'token']);

    $response->assertOk()->assertJson(['status' => 'authenticated']);
    expect($response->json('token'))->not->toBeNull();
    expect($response->json('user.id'))->toBe($user->id);
});

it('registers a new user and issues a token', function () {
    fakeFirebaseIdentity();

    $response = $this->postJson('/api/v1/auth/register', [
        'id_token' => 'token',
        'name' => 'New User',
    ]);

    $response->assertCreated();
    expect($response->json('token'))->not->toBeNull();

    $this->assertDatabaseHas('users', [
        'firebase_uid' => 'firebase-uid-1',
        'phone' => '+919000000001',
        'name' => 'New User',
        'status' => 'registered',
    ]);
});

it('links a referral when a valid referral_code is supplied', function () {
    $referrer = User::factory()->create();
    fakeFirebaseIdentity();

    $this->postJson('/api/v1/auth/register', [
        'id_token' => 'token',
        'name' => 'Referred User',
        'referral_code' => $referrer->referral_code,
    ])->assertCreated();

    $referred = User::query()->where('firebase_uid', 'firebase-uid-1')->firstOrFail();

    expect($referred->referred_by)->toBe($referrer->id);
    $this->assertDatabaseHas('referrals', [
        'referrer_id' => $referrer->id,
        'referred_id' => $referred->id,
        'referred_paid' => false,
    ]);
});

it('rejects registration for an already-registered firebase uid', function () {
    User::factory()->create(['firebase_uid' => 'firebase-uid-1']);
    fakeFirebaseIdentity();

    $this->postJson('/api/v1/auth/register', [
        'id_token' => 'token',
        'name' => 'Duplicate',
    ])->assertStatus(409);
});
