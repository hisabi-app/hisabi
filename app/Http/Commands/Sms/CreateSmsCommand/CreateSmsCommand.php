<?php

namespace App\Http\Commands\Sms\CreateSmsCommand;

class CreateSmsCommand
{
    public function __construct(
        public readonly array $data
    ) {}
}

