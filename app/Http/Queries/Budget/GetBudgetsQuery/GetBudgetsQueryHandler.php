<?php

namespace App\Http\Queries\Budget\GetBudgetsQuery;

use App\Domains\Budget\Services\BudgetService;

class GetBudgetsQueryHandler
{
    public function __construct(
        private readonly BudgetService $budgetService
    ) {}

    public function handle(GetBudgetsQuery $query): GetBudgetsQueryResponse
    {
        $budgets = $this->budgetService->getAll();

        return new GetBudgetsQueryResponse($budgets);
    }
}
