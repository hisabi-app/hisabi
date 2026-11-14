<?php

namespace App\Http\Queries\Budget\GetBudgetDetailQuery;

use App\Http\Resources\BudgetResource;
use App\Http\Resources\TransactionResource;
use Illuminate\Http\JsonResponse;

readonly class GetBudgetDetailQueryResponse
{
    public function __construct(
        private array $detail
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'data' => [
                'budget' => new BudgetResource($this->detail['budget']),
                'category' => $this->detail['category'],
                'brands' => $this->detail['brands'],
                'transactions' => TransactionResource::collection($this->detail['transactions']),
            ],
        ]);
    }
}
