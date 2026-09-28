<?php

namespace App\Http\Queries\Budget\GetBudgetDailySpendingQuery;

use App\Domains\Budget\Models\Budget;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

readonly class GetBudgetDailySpendingQueryResponse
{
    public function __construct(
        private Budget $budget,
        private array $days
    ) {}

    public function toResponse(): JsonResponse
    {
        $startAt = Carbon::parse($this->budget->start_at_date);
        $endAt = Carbon::parse($this->budget->end_at_date);

        return response()->json([
            'data' => [
                'budget_id' => $this->budget->id,
                'start_date' => $startAt->format('Y-m-d'),
                'end_date' => $endAt->format('Y-m-d'),
                'total_days' => (int) $startAt->diffInDays($endAt),
                'days' => $this->days,
            ],
        ]);
    }
}
