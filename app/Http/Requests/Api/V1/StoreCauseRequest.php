<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreCauseRequest extends FormRequest
{
    /**
     * Any authenticated member can raise a help request — this isn't
     * restricted to organizer-tier the way SOS alerts are.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'exists:categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'goal_amount' => ['nullable', 'integer', 'min:0'],
            'deadline' => ['nullable', 'date', 'after:today'],
            'documents' => ['nullable', 'array', 'max:4'],
            'documents.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ];
    }
}
