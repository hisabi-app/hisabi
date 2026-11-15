<?php

namespace App\Http\Commands\Sms\SyncSmsCommand;

readonly class SyncSmsCommand
{
    /**
     * @param array<int, array{guid: string, sender: ?string, received_at: string, text: string}> $messages
     */
    public function __construct(
        public array $messages
    ) {}
}
