<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Queries\Transaction\GetTransactionsQuery\GetTransactionsQuery;
use App\Http\Queries\Transaction\GetTransactionsQuery\GetTransactionsQueryHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private readonly GetTransactionsQueryHandler $getTransactionsQueryHandler
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetTransactionsQuery(
            perPage: (int) $request->get('perPage', 50)
        );

        return $this->getTransactionsQueryHandler->handle($query)->toResponse();
    }
}

