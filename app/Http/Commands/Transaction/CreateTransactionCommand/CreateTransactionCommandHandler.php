<?php

namespace App\Http\Commands\Transaction\CreateTransactionCommand;

use App\Domains\Transaction\Services\TransactionService;
use Illuminate\Support\Facades\DB;

class CreateTransactionCommandHandler
{
    public function __construct(
        private readonly TransactionService $transactionService
    ) {}

    public function handle(CreateTransactionCommand $command): CreateTransactionCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $transaction = $this->transactionService->create($command->data);
            return new CreateTransactionCommandResponse($transaction);
        });
    }
}
