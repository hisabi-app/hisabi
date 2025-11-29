<?php

namespace App\Http\Commands\Category\DeleteCategoryCommand;

use App\Domains\Category\Services\CategoryService;
use Illuminate\Support\Facades\DB;

class DeleteCategoryCommandHandler
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {}

    public function handle(DeleteCategoryCommand $command): DeleteCategoryCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $category = $this->categoryService->delete($command->id);
            return new DeleteCategoryCommandResponse($category);
        });
    }
}
