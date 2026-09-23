<?php

namespace App\Services;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\MulticastSendReport;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class PushNotificationService
{
    public function __construct(private readonly Container $container) {}

    /**
     * Push is best-effort: an unconfigured/unreachable Firebase project must
     * never break the request that triggered the notification, so resolving
     * the Messaging client itself is wrapped alongside the send.
     */
    private function messaging(): ?Messaging
    {
        try {
            return $this->container->make(Messaging::class);
        } catch (\Throwable $e) {
            Log::warning('Firebase Messaging unavailable.', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * @param  string[]  $tokens
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): ?MulticastSendReport
    {
        if ($tokens === [] || ! $messaging = $this->messaging()) {
            return null;
        }

        $message = CloudMessage::new()
            ->withNotification(FcmNotification::create($title, $body))
            ->withData($data);

        try {
            return $messaging->sendMulticast($message, $tokens);
        } catch (\Throwable $e) {
            Log::warning('FCM push send failed.', ['message' => $e->getMessage()]);

            return null;
        }
    }

    /**
     * Broadcasts scale via an FCM topic rather than enumerating every device
     * token. The Flutter app subscribes every signed-in device to $topic.
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        if (! $messaging = $this->messaging()) {
            return false;
        }

        $message = CloudMessage::new()
            ->withTopic($topic)
            ->withNotification(FcmNotification::create($title, $body))
            ->withData($data);

        try {
            $messaging->send($message);

            return true;
        } catch (\Throwable $e) {
            Log::warning('FCM topic push send failed.', ['topic' => $topic, 'message' => $e->getMessage()]);

            return false;
        }
    }
}
