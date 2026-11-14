<?php

namespace App\Http\Commands\Budget\CreateBudgetCommand;

readonly class CreateBudgetCommand
{
    public function __construct(
        public array $data
    ) {}
}
