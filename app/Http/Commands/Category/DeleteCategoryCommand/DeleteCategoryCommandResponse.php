<?php

namespace App\Http\Commands\Category\DeleteCategoryCommand;

use App\Domains\Category\Models\Category;
use Illuminate\Http\JsonResponse;

readonly class DeleteCategoryCommandResponse
{
    public function __construct(
        private Category $category
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'category' => $this->category,
        ]);
    }
}
