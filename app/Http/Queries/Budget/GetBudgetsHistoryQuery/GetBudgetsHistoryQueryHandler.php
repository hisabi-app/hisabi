<?php

namespace App\Http\Queries\Budget\GetBudgetsHistoryQuery;

use App\Domains\Budget\Services\BudgetService;
use Carbon\Carbon;

class GetBudgetsHistoryQueryHandler
{
    // History goes back at most 5 years, current month included.
    private const MAX_MONTHS = 60;

    // Without a range, show the last 2 years, current month included.
    private const DEFAULT_MONTHS = 24;

    public function __construct(
        private readonly BudgetService $budgetService
    ) {}

    public function handle(GetBudgetsHistoryQuery $query): GetBudgetsHistoryQueryResponse
    {
        $earliest = now()->startOfMonth()->subMonths(self::MAX_MONTHS - 1);

        $from = $query->from ? Carbon::parse($query->from)->startOfDay() : now()->startOfMonth()->subMonths(self::DEFAULT_MONTHS - 1);
        $to = $query->to ? Carbon::parse($query->to)->endOfDay() : now();

        $from = $from->max($earliest);
        $to = $to->min(now());

        return new GetBudgetsHistoryQueryResponse(
            $this->budgetService->getHistory($from, $to),
            $from,
            $to,
            $earliest,
            $this->budgetService->getFirstTransactionDate()
        );
    }
}
