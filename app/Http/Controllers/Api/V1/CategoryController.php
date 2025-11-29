<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Queries\Category\GetAllCategoriesQuery\GetAllCategoriesQuery;
use App\Http\Queries\Category\GetAllCategoriesQuery\GetAllCategoriesQueryHandler;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private readonly GetAllCategoriesQueryHandler $getAllCategoriesQueryHandler
    ) {}

    public function all(): JsonResponse
    {
        $query = new GetAllCategoriesQuery();

        return $this->getAllCategoriesQueryHandler->handle($query)->toResponse();
    }
}
