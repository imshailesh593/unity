<?php

namespace App\Models;

use Database\Factories\UserDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'type', 'file_path', 'status', 'rejection_reason', 'reviewed_by', 'reviewed_at'])]
class UserDocument extends Model
{
    /** @use HasFactory<UserDocumentFactory> */
    use HasFactory;

    public const TYPES = ['aadhar_front', 'aadhar_back'];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
