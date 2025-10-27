<?php

namespace App\Http\Commands\Transaction\DeleteTransactionCommand;

use App\Domains\Transaction\Models\Transaction;
use Illuminate\Http\JsonResponse;

readonly class DeleteTransactionCommandResponse
{
    public function __construct(
        private Transaction $transaction
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'transaction' => $this->transaction->load('brand.category'),
        ]);
    }
}
