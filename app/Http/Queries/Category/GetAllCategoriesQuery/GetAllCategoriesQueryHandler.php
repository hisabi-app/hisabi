<?php

namespace App\Http\Queries\Category\GetAllCategoriesQuery;

use App\Domains\Category\Services\CategoryService;

class GetAllCategoriesQueryHandler
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {}

    public function handle(GetAllCategoriesQuery $query): GetAllCategoriesQueryResponse
    {
        $categories = $this->categoryService->getAll();

        return new GetAllCategoriesQueryResponse($categories);
    }
}
