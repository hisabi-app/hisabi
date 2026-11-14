<?php

namespace App\Domains\Home\Services;

use App\Domains\Brand\Models\Brand;
use App\Domains\Budget\Models\Budget;
use App\Domains\Budget\Services\BudgetService;
use App\Domains\Transaction\Models\Transaction;

class HomeService
{
    public const STAGE_NEW = 'new';
    public const STAGE_EARLY = 'early';
    public const STAGE_FULL = 'full';

    public function __construct(
        private readonly BudgetService $budgetService
    ) {}

    /**
     * How far along the user is, so the app can unfold as data arrives:
     * new (no transactions yet), early (less than a month of them) or full.
     */
    public function getStage(): string
    {
        $firstTransactionDate = $this->budgetService->getFirstTransactionDate();

        return match (true) {
            $firstTransactionDate === null => self::STAGE_NEW,
            $firstTransactionDate->gt(now()->subMonth()) => self::STAGE_EARLY,
            default => self::STAGE_FULL,
        };
    }

    /**
     * What the sidebar and dock need on every page: the stage and the counts of things waiting.
     */
    public function getNavigation(): array
    {
        return [
            'stage' => $this->getStage(),
            'has_budgets' => Budget::exists(),
            'counts' => [
                'budgets' => $this->budgetService->getCurrent()->filter(fn (Budget $budget) => $budget->needsAttention())->count(),
                'brands' => $this->uncategorizedBrands()->count(),
            ],
        ];
    }

    public function uncategorizedBrands()
    {
        return Brand::query()->whereNull('category_id')->has('transactions');
    }

    public function hasTransactions(): bool
    {
        return Transaction::exists();
    }
}
