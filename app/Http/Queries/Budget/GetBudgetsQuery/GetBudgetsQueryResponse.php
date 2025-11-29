<?php

namespace App\Http\Queries\Budget\GetBudgetsQuery;

use App\Http\Resources\BudgetResource;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

readonly class GetBudgetsQueryResponse
{
    public function __construct(
        private Collection $budgets
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'data' => BudgetResource::collection($this->budgets),
        ]);
    }
}
