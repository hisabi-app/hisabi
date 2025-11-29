<?php

namespace App\Http\Commands\Category\UpdateCategoryCommand;

use App\Domains\Category\Services\CategoryService;
use Illuminate\Support\Facades\DB;

class UpdateCategoryCommandHandler
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {}

    public function handle(UpdateCategoryCommand $command): UpdateCategoryCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $category = $this->categoryService->update($command->id, $command->data);
            return new UpdateCategoryCommandResponse($category);
        });
    }
}
