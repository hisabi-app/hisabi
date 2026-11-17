<?php

namespace App\Http\Commands\Kiosk\AskKioskQuestionCommand;

readonly class AskKioskQuestionCommand
{
    public function __construct(
        public string $question,
        public ?string $conversationId = null
    ) {}
}
