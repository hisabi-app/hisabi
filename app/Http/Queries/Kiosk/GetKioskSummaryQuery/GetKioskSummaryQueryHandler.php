<?php

namespace App\Http\Queries\Kiosk\GetKioskSummaryQuery;

use App\Domains\Assistant\Services\AssistantService;
use App\Domains\Budget\Services\BudgetService;
use App\Domains\Home\Services\HomeService;
use App\Domains\Transaction\Models\Transaction;
use App\Domains\Transaction\Services\TransactionService;

class GetKioskSummaryQueryHandler
{
    public function __construct(
        private readonly TransactionService $transactionService,
        private readonly BudgetService $budgetService,
        private readonly HomeService $homeService,
        private readonly AssistantService $assistantService
    ) {}

    public function handle(GetKioskSummaryQuery $query): GetKioskSummaryQueryResponse
    {
        $now = now();
        [$lastMonthStart, $sameTimeLastMonth] = $this->homeService->sameTimeLastMonth($now);

        return new GetKioskSummaryQueryResponse(
            spent: $this->transactionService->getExpensesTotal($now->copy()->startOfMonth(), $now),
            spentLastMonthToDate: $this->transactionService->getExpensesTotal($lastMonthStart, $sameTimeLastMonth),
            budgets: $this->budgetService->getCurrent(),
            daily: $this->homeService->getCumulativeDailyExpenses($now->copy()->startOfMonth(), $now),
            lastMonthDaily: $this->homeService->getCumulativeDailyExpenses($lastMonthStart, $lastMonthStart->copy()->endOfMonth()),
            categories: $this->homeService->getExpensesByCategory($now->copy()->startOfMonth(), $now)->all(),
            // The one added last, so the wall shows that a new SMS landed (SMS dates carry no time of day)
            latest: Transaction::query()->with('brand.category')->orderByDesc('id')->first(),
            assistant: $this->assistant()
        );
    }

    /**
     * Whether the Ask button shows (and can listen), with a few questions to ask by tapping.
     */
    private function assistant(): array
    {
        $enabled = $this->assistantService->enabled();

        return [
            'enabled' => $enabled,
            'voice' => $enabled && $this->assistantService->hasVoice(),
            'suggestions' => $enabled ? $this->assistantService->suggestions() : [],
        ];
    }
}
