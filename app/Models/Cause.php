<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'organizer_id', 'category_id', 'title', 'slug', 'excerpt', 'content',
    'featured_image', 'goal_amount', 'raised_amount', 'deadline', 'status', 'verified',
])]
class Cause extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'goal_amount' => 'integer',
            'raised_amount' => 'integer',
            'deadline' => 'date',
            'verified' => 'boolean',
        ];
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function progressPercent(): int
    {
        if ($this->goal_amount <= 0) {
            return 0;
        }

        return (int) min(100, round(($this->raised_amount / $this->goal_amount) * 100));
    }
}
