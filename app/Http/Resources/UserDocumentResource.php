<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'uploaded_at' => $this->created_at,
            'reviewed_at' => $this->reviewed_at,
        ];
    }
}
