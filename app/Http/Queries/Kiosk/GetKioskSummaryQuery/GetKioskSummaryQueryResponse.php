<?php

namespace App\Http\Queries\Kiosk\GetKioskSummaryQuery;

use App\Domains\Transaction\Models\Transaction;
use App\Http\Resources\BudgetResource;
use App\Http\Resources\TransactionResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

readonly class GetKioskSummaryQueryResponse
{
    public function __construct(
        private float $spent,
        private float $spentLastMonthToDate,
        private Collection $budgets,
        private array $daily = [],
        private array $lastMonthDaily = [],
        private ?Transaction $latest = null
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'data' => [
                'spent' => $this->spent,
                'spent_last_month_to_date' => $this->spentLastMonthToDate,
                'budgets' => BudgetResource::collection($this->budgets),
                'daily' => $this->daily,
                'last_month_daily' => $this->lastMonthDaily,
                'latest' => $this->latest ? new TransactionResource($this->latest) : null,
                // When it was added; its own date often has no time of day
                'latest_at' => $this->latest?->updated_at?->toIso8601String(),
            ],
        ]);
    }
}
