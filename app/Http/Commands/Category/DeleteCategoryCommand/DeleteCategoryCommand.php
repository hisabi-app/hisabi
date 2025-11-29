<?php

namespace App\Http\Commands\Category\DeleteCategoryCommand;

readonly class DeleteCategoryCommand
{
    public function __construct(
        public int $id
    ) {}
}
