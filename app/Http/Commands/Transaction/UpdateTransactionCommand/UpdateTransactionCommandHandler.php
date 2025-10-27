<?php

namespace App\Http\Commands\Transaction\UpdateTransactionCommand;

use App\Domains\Transaction\Services\TransactionService;
use Illuminate\Support\Facades\DB;

class UpdateTransactionCommandHandler
{
    public function __construct(
        private readonly TransactionService $transactionService
    ) {}

    public function handle(UpdateTransactionCommand $command): UpdateTransactionCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $transaction = $this->transactionService->update($command->id, $command->data);
            return new UpdateTransactionCommandResponse($transaction);
        });
    }
}
