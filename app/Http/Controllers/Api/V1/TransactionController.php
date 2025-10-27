<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Queries\Transaction\GetTransactionsQuery\GetTransactionsQuery;
use App\Http\Queries\Transaction\GetTransactionsQuery\GetTransactionsQueryHandler;
use App\Http\Commands\Transaction\CreateTransactionCommand\CreateTransactionCommand;
use App\Http\Commands\Transaction\CreateTransactionCommand\CreateTransactionCommandHandler;
use App\Http\Commands\Transaction\UpdateTransactionCommand\UpdateTransactionCommand;
use App\Http\Commands\Transaction\UpdateTransactionCommand\UpdateTransactionCommandHandler;
use App\Http\Requests\Api\V1\CreateTransactionRequest;
use App\Http\Requests\Api\V1\UpdateTransactionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private readonly GetTransactionsQueryHandler $getTransactionsQueryHandler,
        private readonly CreateTransactionCommandHandler $createTransactionCommandHandler,
        private readonly UpdateTransactionCommandHandler $updateTransactionCommandHandler
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetTransactionsQuery(
            perPage: (int) $request->get('perPage', 50)
        );

        return $this->getTransactionsQueryHandler->handle($query)->toResponse();
    }

    public function store(CreateTransactionRequest $request): JsonResponse
    {
        $command = new CreateTransactionCommand(
            data: $request->validated()
        );

        return $this->createTransactionCommandHandler->handle($command)->toResponse();
    }

    public function update(UpdateTransactionRequest $request, int $id): JsonResponse
    {
        $command = new UpdateTransactionCommand(
            id: $id,
            data: $request->validated()
        );

        return $this->updateTransactionCommandHandler->handle($command)->toResponse();
    }
}

