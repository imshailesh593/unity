<?php

namespace App\Http\Resources;

use App\Services\ActivationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $activation = app(ActivationService::class);

        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'status' => $this->status,
            'has_paid' => $this->has_paid,
            'referral_code' => $this->referral_code,
            'referral_link' => url('/r/'.$this->referral_code),
            'author_tier' => $this->author_tier,
            'activation_progress' => [
                'has_paid' => $this->has_paid,
                'activation_fee' => $activation->activationFee(),
                'referrals_required' => $activation->requiredReferrals(),
                'referrals_paid' => $activation->paidReferralsCount($this->resource),
            ],
            'created_at' => $this->created_at,
        ];
    }
}
