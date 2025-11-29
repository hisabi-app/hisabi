<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Commands\Category\CreateCategoryCommand\CreateCategoryCommand;
use App\Http\Commands\Category\CreateCategoryCommand\CreateCategoryCommandHandler;
use App\Http\Queries\Category\GetAllCategoriesQuery\GetAllCategoriesQuery;
use App\Http\Queries\Category\GetAllCategoriesQuery\GetAllCategoriesQueryHandler;
use App\Http\Requests\Api\V1\CreateCategoryRequest;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private readonly GetAllCategoriesQueryHandler $getAllCategoriesQueryHandler,
        private readonly CreateCategoryCommandHandler $createCategoryCommandHandler
    ) {}

    public function all(): JsonResponse
    {
        $query = new GetAllCategoriesQuery();

        return $this->getAllCategoriesQueryHandler->handle($query)->toResponse();
    }

    public function store(CreateCategoryRequest $request): JsonResponse
    {
        $command = new CreateCategoryCommand(
            data: $request->validated()
        );

        return $this->createCategoryCommandHandler->handle($command)->toResponse();
    }
}
