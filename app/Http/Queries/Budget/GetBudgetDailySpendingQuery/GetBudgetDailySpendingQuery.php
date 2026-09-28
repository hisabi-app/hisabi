<?php

namespace App\Http\Queries\Budget\GetBudgetDailySpendingQuery;

class GetBudgetDailySpendingQuery
{
    public function __construct(
        public readonly int $budgetId
    ) {}
}
