<?php

namespace App\Http\Queries\Budget\GetBudgetsHistoryQuery;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

readonly class GetBudgetsHistoryQueryResponse
{
    public function __construct(
        private array $budgets,
        private Carbon $from,
        private Carbon $to,
        private Carbon $earliest,
        private ?Carbon $firstTransactionDate
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'data' => $this->budgets,
            'meta' => [
                'from' => $this->from->format('Y-m-d'),
                'to' => $this->to->format('Y-m-d'),
                'earliest_date' => $this->earliest->format('Y-m-d'),
                'first_transaction_date' => $this->firstTransactionDate?->format('Y-m-d'),
            ],
        ]);
    }
}
