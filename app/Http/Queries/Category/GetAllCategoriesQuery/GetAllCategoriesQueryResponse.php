<?php

namespace App\Http\Queries\Category\GetAllCategoriesQuery;

use App\Http\Resources\CategoryResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

readonly class GetAllCategoriesQueryResponse
{
    public function __construct(
        private Collection $categories
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'data' => CategoryResource::collection($this->categories),
        ]);
    }
}
