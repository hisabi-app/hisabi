<?php

namespace App\Http\Commands\Sms\UpdateSmsCommand;

readonly class UpdateSmsCommand
{
    public function __construct(
        public int $id,
        public array $data
    ) {}
}
