<?php

namespace App\Http\Queries\Transaction\GetTransactionsQuery;

class GetTransactionsQuery
{
    public function __construct(
        public readonly int $perPage
    ) {}
}

