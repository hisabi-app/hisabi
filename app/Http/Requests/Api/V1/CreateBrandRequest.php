<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|integer|exists:categories,id',
            'aliases' => 'sometimes|nullable|array|max:20',
            'aliases.*' => 'nullable|string|max:100',
        ];
    }

    /**
     * Aliases come back trimmed, without blanks or repeats, and as null when there are none.
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if ($key === null && array_key_exists('aliases', $data)) {
            $aliases = collect($data['aliases'] ?? [])
                ->map(fn ($alias) => trim((string) $alias))
                ->filter(fn ($alias) => trim($alias, " *") !== '')
                ->unique(fn ($alias) => mb_strtolower($alias))
                ->values()
                ->all();

            $data['aliases'] = $aliases === [] ? null : $aliases;
        }

        return $data;
    }
}
