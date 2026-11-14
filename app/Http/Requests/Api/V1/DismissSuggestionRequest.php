<?php

namespace App\Http\Requests\Api\V1;

use App\Domains\Home\Services\HomeService;
use Illuminate\Foundation\Http\FormRequest;

class DismissSuggestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'required|in:' . HomeService::BUDGET_SUGGESTION,
        ];
    }
}
