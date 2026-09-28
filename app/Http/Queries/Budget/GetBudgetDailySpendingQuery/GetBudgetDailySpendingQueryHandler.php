<?php

namespace App\Http\Queries\Budget\GetBudgetDailySpendingQuery;

use App\Domains\Budget\Models\Budget;
use App\Domains\Budget\Services\BudgetService;

class GetBudgetDailySpendingQueryHandler
{
    public function __construct(
        private readonly BudgetService $budgetService
    ) {}

    public function handle(GetBudgetDailySpendingQuery $query): GetBudgetDailySpendingQueryResponse
    {
        $budget = Budget::findOrFail($query->budgetId);

        return new GetBudgetDailySpendingQueryResponse(
            $budget,
            $this->budgetService->getDailySpending($budget)
        );
    }
}
