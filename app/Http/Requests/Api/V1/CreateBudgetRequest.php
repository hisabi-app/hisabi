<?php

namespace App\Http\Requests\Api\V1;

use App\Domains\Budget\Models\Budget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|gt:0',
            'reoccurrence' => [
                'required',
                'string',
                Rule::in([Budget::DAILY, Budget::WEEKLY, Budget::MONTHLY, Budget::YEARLY, Budget::CUSTOM]),
            ],
            'start_at' => 'required|date',
            // The last day of a custom budget; recurring budgets don't end
            'end_at' => 'exclude_unless:reoccurrence,' . Budget::CUSTOM . '|required|date|after_or_equal:start_at',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'integer|distinct|exists:categories,id',
        ];
    }
}
