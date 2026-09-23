<?php

use App\Events\UserActivated;
use App\Models\Referral;
use App\Models\Setting;
use App\Models\User;
use App\Services\ActivationService;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Setting::set('activation_fee', 199);
    Setting::set('required_referrals', 2);
});

function makePaidReferral(User $referrer, bool $paid = true): Referral
{
    $referred = User::factory()->create();

    return Referral::create([
        'referrer_id' => $referrer->id,
        'referred_id' => $referred->id,
        'referred_paid' => $paid,
    ]);
}

it('stays pending when unpaid regardless of referral count', function () {
    $user = User::factory()->create(['has_paid' => false, 'status' => 'registered']);
    makePaidReferral($user);
    makePaidReferral($user);

    $activated = app(ActivationService::class)->checkAndActivate($user);

    expect($activated)->toBeFalse();
    expect($user->fresh()->status)->not->toBe('active');
});

it('stays pending when paid but referrals below threshold', function () {
    $user = User::factory()->create(['has_paid' => true, 'status' => 'pending']);
    makePaidReferral($user);

    $activated = app(ActivationService::class)->checkAndActivate($user);

    expect($activated)->toBeFalse();
    expect($user->fresh()->status)->not->toBe('active');
});

it('activates when paid and required referrals are paid, firing UserActivated', function () {
    Event::fake([UserActivated::class]);

    $user = User::factory()->create(['has_paid' => true, 'status' => 'pending']);
    makePaidReferral($user);
    makePaidReferral($user);

    $activated = app(ActivationService::class)->checkAndActivate($user);

    expect($activated)->toBeTrue();
    expect($user->fresh()->status)->toBe('active');
    Event::assertDispatched(UserActivated::class, fn ($event) => $event->user->is($user));
});

it('does not count unpaid referrals toward the threshold', function () {
    $user = User::factory()->create(['has_paid' => true, 'status' => 'pending']);
    makePaidReferral($user, paid: true);
    makePaidReferral($user, paid: false);

    $activated = app(ActivationService::class)->checkAndActivate($user);

    expect($activated)->toBeFalse();
});

it('is idempotent once already active', function () {
    Event::fake([UserActivated::class]);

    $user = User::factory()->create(['has_paid' => true, 'status' => 'active']);
    makePaidReferral($user);
    makePaidReferral($user);

    $activated = app(ActivationService::class)->checkAndActivate($user);

    expect($activated)->toBeFalse();
    Event::assertNotDispatched(UserActivated::class);
});

it('respects a configurable required referral count', function () {
    Setting::set('required_referrals', 3);

    $user = User::factory()->create(['has_paid' => true, 'status' => 'pending']);
    makePaidReferral($user);
    makePaidReferral($user);

    expect(app(ActivationService::class)->checkAndActivate($user))->toBeFalse();

    makePaidReferral($user);

    expect(app(ActivationService::class)->checkAndActivate($user))->toBeTrue();
});
