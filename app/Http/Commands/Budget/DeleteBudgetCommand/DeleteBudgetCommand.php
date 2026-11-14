<?php

namespace App\Http\Commands\Budget\DeleteBudgetCommand;

readonly class DeleteBudgetCommand
{
    public function __construct(
        public int $id
    ) {}
}
