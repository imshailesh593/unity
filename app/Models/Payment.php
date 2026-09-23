<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'cause_id', 'amount', 'gateway', 'gateway_txn_id', 'status', 'purpose'])]
class Payment extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cause(): BelongsTo
    {
        return $this->belongsTo(Cause::class);
    }
}
