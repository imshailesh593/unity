<?php

namespace App\Listeners;

use App\Events\UserActivated;
use App\Services\NotificationDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendActivationNotifications implements ShouldQueue
{
    public function __construct(private readonly NotificationDispatcher $notifications) {}

    public function handle(UserActivated $event): void
    {
        $this->notifications->notify(
            user: $event->user,
            type: 'activation',
            title: 'Account activated',
            body: 'Your Unity account is now fully active. Enjoy full app access.',
            smsTemplateId: config('services.msg91.templates.activation_success'),
        );
    }
}
