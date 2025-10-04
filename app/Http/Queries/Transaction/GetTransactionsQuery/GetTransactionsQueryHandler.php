<?php

namespace App\Http\Queries\Transaction\GetTransactionsQuery;

use App\Domains\Transaction\Services\TransactionService;

class GetTransactionsQueryHandler
{
    public function __construct(
        private readonly TransactionService $transactionService
    ) {}

    public function handle(GetTransactionsQuery $query): GetTransactionsQueryResponse
    {
        $transactions = $this->transactionService->getPaginated(
            perPage: $query->perPage
        );

        return new GetTransactionsQueryResponse($transactions);
    }
}

