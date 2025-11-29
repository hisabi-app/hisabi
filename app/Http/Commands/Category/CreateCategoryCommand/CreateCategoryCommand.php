<?php

namespace App\Http\Commands\Category\CreateCategoryCommand;

readonly class CreateCategoryCommand
{
    public function __construct(
        public array $data
    ) {}
}
