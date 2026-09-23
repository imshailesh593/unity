<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CauseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image' => $this->featured_image
                ? (str($this->featured_image)->startsWith(['http://', 'https://'])
                    ? $this->featured_image
                    : asset('storage/'.$this->featured_image))
                : null,
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ]),
            'organizer_name' => $this->whenLoaded('organizer', fn () => $this->organizer?->name),
            'goal_amount' => $this->goal_amount,
            'raised_amount' => $this->raised_amount,
            'progress_percent' => $this->progressPercent(),
            'deadline' => $this->deadline,
            'verified' => $this->verified,
            'status' => $this->status,
            'created_at' => $this->created_at,
        ];
    }
}
