<?php

namespace App\Http\Commands\Sms\SyncSmsCommand;

use App\Domains\Sms\Services\SmsService;
use Illuminate\Support\Facades\DB;

class SyncSmsCommandHandler
{
    public function __construct(
        private readonly SmsService $smsService
    ) {}

    public function handle(SyncSmsCommand $command): SyncSmsCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $results = collect($command->messages)
                ->map(fn (array $message) => $this->smsService->sync($message));

            return new SyncSmsCommandResponse($results);
        });
    }
}
