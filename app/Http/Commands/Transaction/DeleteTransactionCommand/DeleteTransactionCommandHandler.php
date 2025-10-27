<?php

namespace App\Http\Commands\Transaction\DeleteTransactionCommand;

use App\Domains\Transaction\Services\TransactionService;
use Illuminate\Support\Facades\DB;

class DeleteTransactionCommandHandler
{
    public function __construct(
        private readonly TransactionService $transactionService
    ) {}

    public function handle(DeleteTransactionCommand $command): DeleteTransactionCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $transaction = $this->transactionService->delete($command->id);
            return new DeleteTransactionCommandResponse($transaction);
        });
    }
}
