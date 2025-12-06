<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'password' => [
                'sometimes',
                'nullable',
                'string',
                Password::min(8)
                    ->letters()
                    ->numbers(),
            ],
        ];
    }
}
