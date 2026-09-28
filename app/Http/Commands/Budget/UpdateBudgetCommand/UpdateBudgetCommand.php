<?php

namespace App\Http\Commands\Budget\UpdateBudgetCommand;

readonly class UpdateBudgetCommand
{
    public function __construct(
        public int $id,
        public array $data
    ) {}
}
