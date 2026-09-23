<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreSosAlertRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAuthor();
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'in:blood,organ,medication,other'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'location' => ['required', 'string', 'max:255'],
            'contact_info' => ['nullable', 'string', 'max:255'],
        ];
    }
}
