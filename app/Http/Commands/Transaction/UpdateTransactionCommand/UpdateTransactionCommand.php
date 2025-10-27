<?php

namespace App\Http\Commands\Transaction\UpdateTransactionCommand;

readonly class UpdateTransactionCommand
{
    public function __construct(
        public int $id,
        public array $data
    ) {}
}
