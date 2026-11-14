<?php

namespace App\Http\Queries\Home\GetHomeQuery;

use App\Http\Resources\BudgetResource;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\JsonResponse;

readonly class GetHomeQueryResponse
{
    public function __construct(
        private array $home
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'data' => [
                ...$this->home,
                'budgets' => BudgetResource::collection($this->home['budgets']),
                'recent' => TransactionResource::collection($this->home['recent']),
            ],
        ]);
    }
}
