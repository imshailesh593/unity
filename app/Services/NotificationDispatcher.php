<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\SosAlert;
use App\Models\User;

class NotificationDispatcher
{
    public function __construct(
        private readonly PushNotificationService $push,
        private readonly SmsGatewayService $sms,
    ) {}

    /**
     * Always records an in-app notification. Push/SMS are best-effort side
     * channels — their failure must never block the in-app record or the
     * caller's transaction.
     */
    public function notify(
        User $user,
        string $type,
        string $title,
        string $body,
        ?string $smsTemplateId = null,
        array $smsVariables = [],
    ): Notification {
        $notification = Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'body' => $body,
            'type' => $type,
        ]);

        $tokens = $user->deviceTokens()->pluck('token')->all();
        $this->push->sendToTokens($tokens, $title, $body, ['type' => $type]);

        if ($smsTemplateId) {
            $this->sms->send($user->phone, $smsTemplateId, $smsVariables);
        }

        return $notification;
    }

    /**
     * Admin broadcast: a single notification row with user_id = null (per
     * the schema's "nullable = broadcast" convention) delivered to every
     * device subscribed to the FCM "broadcast" topic. No per-user SMS —
     * broadcasts are push/in-app only by design.
     */
    public function broadcast(string $title, string $body): Notification
    {
        $notification = Notification::create([
            'user_id' => null,
            'title' => $title,
            'body' => $body,
            'type' => 'admin_broadcast',
        ]);

        $this->push->sendToTopic('broadcast', $title, $body, ['type' => 'admin_broadcast']);

        return $notification;
    }

    /**
     * SOS alerts are push-first by design — delivered to a dedicated "sos"
     * topic (separate from general broadcasts) the moment they're created,
     * ahead of any moderation review.
     */
    public function broadcastSos(SosAlert $alert): Notification
    {
        $title = 'Urgent: '.$alert->title;

        $notification = Notification::create([
            'user_id' => null,
            'title' => $title,
            'body' => $alert->location,
            'type' => 'sos_alert',
        ]);

        $this->push->sendToTopic('sos', $title, $alert->location, [
            'type' => 'sos_alert',
            'sos_alert_id' => (string) $alert->id,
            'category' => $alert->category,
        ]);

        return $notification;
    }
}
