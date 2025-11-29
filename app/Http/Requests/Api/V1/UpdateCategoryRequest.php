<?php

namespace App\Http\Requests\Api\V1;

use App\Domains\Category\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'type' => [
                'required',
                'string',
                Rule::in([Category::INCOME, Category::EXPENSES, Category::SAVINGS, Category::INVESTMENT]),
            ],
            'color' => 'required|string|max:50',
            'icon' => 'required|string|max:50',
        ];
    }
}
