<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreCauseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isOrganizer();
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'goal_amount' => ['required', 'integer', 'min:1000'],
            'deadline' => ['nullable', 'date', 'after:today'],
        ];
    }
}
