<?php

use App\Models\User;
use App\Services\NotificationDispatcher;

it('creates the in-app notification even when Firebase/SMS are unconfigured', function () {
    $user = User::factory()->create();

    $notification = app(NotificationDispatcher::class)->notify(
        user: $user,
        type: 'activation',
        title: 'Account activated',
        body: 'Welcome aboard.',
    );

    expect($notification->exists)->toBeTrue();
    $this->assertDatabaseHas('notifications', [
        'user_id' => $user->id,
        'type' => 'activation',
    ]);
});

it('records a broadcast as a single row with a null user_id', function () {
    $notification = app(NotificationDispatcher::class)->broadcast('New cause update', 'Check out our latest impact report.');

    expect($notification->user_id)->toBeNull();
    $this->assertDatabaseHas('notifications', [
        'id' => $notification->id,
        'user_id' => null,
        'type' => 'admin_broadcast',
    ]);
});
