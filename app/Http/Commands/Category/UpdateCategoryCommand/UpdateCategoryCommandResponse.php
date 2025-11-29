<?php

namespace App\Http\Commands\Category\UpdateCategoryCommand;

use App\Domains\Category\Models\Category;
use Illuminate\Http\JsonResponse;

readonly class UpdateCategoryCommandResponse
{
    public function __construct(
        private Category $category
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'category' => $this->category->loadCount('transactions'),
        ]);
    }
}
