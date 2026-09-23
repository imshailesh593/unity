<?php

use App\Models\SosAlert;
use App\Models\User;
use App\Services\NotificationDispatcher;

it('lists only active, non-expired alerts', function () {
    SosAlert::factory()->create(['status' => 'active', 'title' => 'Live Alert', 'expires_at' => now()->addDay()]);
    SosAlert::factory()->create(['status' => 'resolved', 'title' => 'Resolved Alert', 'expires_at' => now()->addDay()]);
    SosAlert::factory()->create(['status' => 'active', 'title' => 'Expired Alert', 'expires_at' => now()->subDay()]);

    $response = $this->getJson('/api/v1/sos');

    $titles = collect($response->json('data'))->pluck('title');
    expect($titles)->toContain('Live Alert')
        ->not->toContain('Resolved Alert')
        ->not->toContain('Expired Alert');
});

it('rejects SOS creation from a member who is not an approved author', function () {
    $member = User::factory()->create();

    $this->actingAs($member, 'sanctum')
        ->postJson('/api/v1/sos', [
            'category' => 'blood',
            'title' => 'O+ needed',
            'description' => 'Urgent need at hospital',
            'location' => 'Pandharpur',
        ])->assertForbidden();
});

it('lets an approved author create an SOS alert and broadcasts it', function () {
    $this->mock(NotificationDispatcher::class, function ($mock) {
        $mock->shouldReceive('broadcastSos')->once();
    });

    $author = User::factory()->author()->create();

    $response = $this->actingAs($author, 'sanctum')
        ->postJson('/api/v1/sos', [
            'category' => 'blood',
            'title' => 'O+ needed',
            'description' => 'Urgent need at hospital',
            'location' => 'Pandharpur',
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('sos_alerts', [
        'author_id' => $author->id,
        'title' => 'O+ needed',
        'status' => 'active',
    ]);
});

it('allows an organizer to create SOS alerts too, since the tier is cumulative', function () {
    $this->mock(NotificationDispatcher::class, function ($mock) {
        $mock->shouldReceive('broadcastSos')->once();
    });

    $organizer = User::factory()->organizer()->create();

    $this->actingAs($organizer, 'sanctum')
        ->postJson('/api/v1/sos', [
            'category' => 'medication',
            'title' => 'Insulin needed',
            'description' => 'Urgent',
            'location' => 'Solapur',
        ])->assertCreated();
});
