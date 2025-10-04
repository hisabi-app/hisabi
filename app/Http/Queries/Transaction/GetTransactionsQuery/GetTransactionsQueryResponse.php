<?php

namespace App\Http\Queries\Transaction\GetTransactionsQuery;

use App\Http\Resources\TransactionResource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;

readonly class GetTransactionsQueryResponse
{
    public function __construct(
        private LengthAwarePaginator $transactions
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'data' => TransactionResource::collection($this->transactions->items()),
            'paginatorInfo' => [
                'hasMorePages' => $this->transactions->hasMorePages(),
                'currentPage' => $this->transactions->currentPage(),
                'lastPage' => $this->transactions->lastPage(),
                'perPage' => $this->transactions->perPage(),
                'total' => $this->transactions->total(),
            ],
        ]);
    }
}

