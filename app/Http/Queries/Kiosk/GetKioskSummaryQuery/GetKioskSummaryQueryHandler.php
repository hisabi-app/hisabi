<?php

namespace App\Http\Queries\Kiosk\GetKioskSummaryQuery;

use App\Domains\Budget\Services\BudgetService;
use App\Domains\Home\Services\HomeService;
use App\Domains\Transaction\Models\Transaction;
use App\Domains\Transaction\Services\TransactionService;

class GetKioskSummaryQueryHandler
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly BudgetService $budgetService,
        private readonly HomeService $homeService
    ) {}

    public function handle(GetKioskSummaryQuery $query): GetKioskSummaryQueryResponse
    {
        $now = now();
        // The same moment last month, e.g. Mar 31 18:00 → Feb 28 18:00
        $sameTimeLastMonth = $now->copy()->subMonthNoOverflow();

        $lastMonthStart = $sameTimeLastMonth->copy()->startOfMonth();

        return new GetKioskSummaryQueryResponse(
            spent: $this->transactionService->getExpensesTotal($now->copy()->startOfMonth(), $now),
            spentLastMonthToDate: $this->transactionService->getExpensesTotal($lastMonthStart, $sameTimeLastMonth),
            budgets: $this->budgetService->getCurrent(),
            daily: $this->homeService->getCumulativeDailyExpenses($now->copy()->startOfMonth(), $now),
            lastMonthDaily: $this->homeService->getCumulativeDailyExpenses($lastMonthStart, $lastMonthStart->copy()->endOfMonth()),
            // So the wall shows that a new SMS landed
            latest: Transaction::query()->with('brand.category')->where('created_at', '<=', $now)->orderByDesc('created_at')->orderByDesc('id')->first()
        );
    }
}
