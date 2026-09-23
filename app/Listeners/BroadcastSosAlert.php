<?php

namespace App\Listeners;

use App\Events\SosAlertCreated;
use App\Services\NotificationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

class BroadcastSosAlert implements ShouldQueue
{
    public function __construct(private readonly NotificationDispatcher $notifications) {}

    public function handle(SosAlertCreated $event): void
    {
        $this->notifications->broadcastSos($event->alert);
    }
}
