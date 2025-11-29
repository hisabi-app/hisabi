<?php

namespace App\Http\Commands\AI\ChatCommand;

class ChatCommand
{
    public function __construct(
        public readonly array $messages
    ) {}
}
