<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'gateway', 'client_id', 'client_secret', 'client_version',
    'sandbox_client_id', 'sandbox_client_secret', 'sandbox_client_version',
    'webhook_username', 'webhook_password', 'is_sandbox', 'is_active',
])]
#[Hidden(['client_secret', 'sandbox_client_secret', 'webhook_password'])]
class PaymentGatewaySetting extends Model
{
    protected function casts(): array
    {
        return [
            'client_secret' => 'encrypted',
            'sandbox_client_secret' => 'encrypted',
            'webhook_password' => 'encrypted',
            'is_sandbox' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public static function forGateway(string $gateway = 'phonepe'): self
    {
        return static::query()->firstOrCreate(['gateway' => $gateway]);
    }

    public function activeClientId(): ?string
    {
        return $this->is_sandbox ? $this->sandbox_client_id : $this->client_id;
    }

    public function activeClientSecret(): ?string
    {
        return $this->is_sandbox ? $this->sandbox_client_secret : $this->client_secret;
    }

    public function activeClientVersion(): ?string
    {
        return $this->is_sandbox ? $this->sandbox_client_version : $this->client_version;
    }

    /**
     * @return string One of \PhonePe\Env's UAT or PRODUCTION constants.
     */
    public function activeEnv(): string
    {
        return $this->is_sandbox ? \PhonePe\Env::UAT : \PhonePe\Env::PRODUCTION;
    }
}
