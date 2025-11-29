<?php

namespace App\Http\Commands\Category\UpdateCategoryCommand;

readonly class UpdateCategoryCommand
{
    public function __construct(
        public int $id,
        public array $data
    ) {}
}
