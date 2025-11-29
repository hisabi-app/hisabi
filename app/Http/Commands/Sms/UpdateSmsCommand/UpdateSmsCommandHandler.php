<?php

namespace App\Http\Commands\Sms\UpdateSmsCommand;

use App\Domains\Sms\Services\SmsService;
use Illuminate\Support\Facades\DB;

class UpdateSmsCommandHandler
{
    public function __construct(
        private readonly SmsService $smsService
    ) {}

    public function handle(UpdateSmsCommand $command): UpdateSmsCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $sms = $this->smsService->update($command->id, $command->data);
            return new UpdateSmsCommandResponse($sms);
        });
    }
}
