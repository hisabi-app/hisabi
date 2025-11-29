<?php

namespace App\Http\Commands\Category\CreateCategoryCommand;

use App\Domains\Category\Services\CategoryService;
use Illuminate\Support\Facades\DB;

class CreateCategoryCommandHandler
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {}

    public function handle(CreateCategoryCommand $command): CreateCategoryCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $category = $this->categoryService->create($command->data);
            return new CreateCategoryCommandResponse($category);
        });
    }
}
