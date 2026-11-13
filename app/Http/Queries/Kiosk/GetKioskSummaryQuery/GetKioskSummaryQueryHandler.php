<?php

namespace App\Http\Queries\Kiosk\GetKioskSummaryQuery;

use App\Domains\Budget\Services\BudgetService;
use App\Domains\Transaction\Services\TransactionService;

class GetKioskSummaryQueryHandler
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly BudgetService $budgetService
    ) {}

    public function handle(GetKioskSummaryQuery $query): GetKioskSummaryQueryResponse
    {
        $now = now();
        // The same moment last month, e.g. Mar 31 18:00 → Feb 28 18:00
        $sameTimeLastMonth = $now->copy()->subMonthNoOverflow();

        return new GetKioskSummaryQueryResponse(
            spent: $this->transactionService->getExpensesTotal($now->copy()->startOfMonth(), $now),
            spentLastMonthToDate: $this->transactionService->getExpensesTotal($sameTimeLastMonth->copy()->startOfMonth(), $sameTimeLastMonth),
            budgets: $this->budgetService->getCurrent()
        );
    }
}
