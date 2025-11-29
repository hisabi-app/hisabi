<?php

namespace App\Http\Commands\Sms\DeleteSmsCommand;

readonly class DeleteSmsCommand
{
    public function __construct(
        public int $id
    ) {}
}
