<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around MSG91's send-SMS API. India-only transactional SMS
 * requires DLT-registered templates — $templateId must match a template
 * approved in the MSG91 dashboard; $variables fill its placeholders.
 */
class SmsGatewayService
{
    public function send(string $phone, string $templateId, array $variables = []): bool
    {
        $authKey = config('services.msg91.auth_key');

        if (! $authKey) {
            Log::info('MSG91 not configured — skipping SMS send.', ['phone' => $phone, 'template' => $templateId]);

            return false;
        }

        $response = Http::withHeaders(['authkey' => $authKey])
            ->post('https://control.msg91.com/api/v5/flow/', [
                'template_id' => $templateId,
                'sender' => config('services.msg91.sender_id'),
                'recipients' => [
                    array_merge(['mobiles' => $phone], $variables),
                ],
            ]);

        if ($response->failed()) {
            Log::warning('MSG91 SMS send failed.', ['phone' => $phone, 'response' => $response->body()]);
        }

        return $response->successful();
    }
}
