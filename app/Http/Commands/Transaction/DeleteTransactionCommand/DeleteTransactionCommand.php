<?php

namespace App\Http\Commands\Transaction\DeleteTransactionCommand;

readonly class DeleteTransactionCommand
{
    public function __construct(
        public int $id
    ) {}
}
