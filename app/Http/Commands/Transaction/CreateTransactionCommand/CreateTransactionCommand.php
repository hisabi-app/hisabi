<?php

namespace App\Http\Commands\Transaction\CreateTransactionCommand;

readonly class CreateTransactionCommand
{
    public function __construct(
        public array $data
    ) {}
}
