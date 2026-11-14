<?php

namespace App\Http\Queries\Budget\GetBudgetDetailQuery;

class GetBudgetDetailQuery
{
    public function __construct(
        public readonly int $id
    ) {}
}
