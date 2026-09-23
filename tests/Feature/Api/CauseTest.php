<?php

use App\Models\Cause;
use App\Models\User;
use App\Services\PaymentGatewayService;

it('lists only published causes', function () {
    Cause::factory()->create(['status' => 'published', 'title' => 'Live Cause', 'slug' => 'live-cause']);
    Cause::factory()->create(['status' => 'draft', 'title' => 'Draft Cause', 'slug' => 'draft-cause']);

    $response = $this->getJson('/api/v1/causes');

    $response->assertOk();
    $slugs = collect($response->json('data'))->pluck('slug');
    expect($slugs)->toContain('live-cause')->not->toContain('draft-cause');
});

it('shows a single published cause with progress percent', function () {
    Cause::factory()->create([
        'status' => 'published',
        'slug' => 'roof-rebuild',
        'goal_amount' => 100000,
        'raised_amount' => 45000,
    ]);

    $this->getJson('/api/v1/causes/roof-rebuild')
        ->assertOk()
        ->assertJsonPath('data.progress_percent', 45);
});

it('rejects cause creation from a non-organizer', function () {
    $member = User::factory()->create();

    $this->actingAs($member, 'sanctum')
        ->postJson('/api/v1/causes', [
            'title' => 'New Cause',
            'content' => 'Details',
            'goal_amount' => 50000,
        ])->assertForbidden();
});

it('allows an organizer to create a cause, starting as pending_review', function () {
    $organizer = User::factory()->organizer()->create();

    $response = $this->actingAs($organizer, 'sanctum')
        ->postJson('/api/v1/causes', [
            'title' => 'New Cause',
            'content' => 'Details of the cause',
            'goal_amount' => 50000,
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('causes', [
        'title' => 'New Cause',
        'organizer_id' => $organizer->id,
        'status' => 'pending_review',
    ]);
});

it('lets an authenticated user initiate a contribution to a published cause', function () {
    $this->mock(PaymentGatewayService::class, function ($mock) {
        $mock->shouldReceive('createOrder')->andReturn(['id' => 'order_cause_1', 'amount' => 500, 'currency' => 'INR']);
    });

    $cause = Cause::factory()->create(['status' => 'published', 'slug' => 'help-anita']);
    $contributor = User::factory()->create();

    $response = $this->actingAs($contributor, 'sanctum')
        ->postJson('/api/v1/causes/help-anita/contribute', ['amount' => 500]);

    $response->assertCreated()->assertJsonPath('order_id', 'order_cause_1');

    $this->assertDatabaseHas('payments', [
        'user_id' => $contributor->id,
        'cause_id' => $cause->id,
        'purpose' => 'cause_contribution',
        'amount' => 500,
        'status' => 'pending',
    ]);
});
