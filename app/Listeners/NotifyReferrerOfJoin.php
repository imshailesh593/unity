<?php

namespace App\Listeners;

use App\Events\ReferralJoined;
use App\Services\NotificationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyReferrerOfJoin implements ShouldQueue
{
    public function __construct(private readonly NotificationDispatcher $notifications) {}

    public function handle(ReferralJoined $event): void
    {
        $this->notifications->notify(
            user: $event->referrer,
            type: 'referral_joined',
            title: 'Someone joined using your link',
            body: "{$event->referred->name} just signed up using your referral link.",
        );
    }
}
