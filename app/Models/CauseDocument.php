<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cause_id', 'file_path'])]
class CauseDocument extends Model
{
    use HasFactory;

    public function cause(): BelongsTo
    {
        return $this->belongsTo(Cause::class);
    }
}
