<?php

namespace App\Http\Commands\Sms\DeleteSmsCommand;

use App\Domains\Sms\Services\SmsService;
use Illuminate\Support\Facades\DB;

class DeleteSmsCommandHandler
{
    public function __construct(
        private readonly SmsService $smsService
    ) {}

    public function handle(DeleteSmsCommand $command): DeleteSmsCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $sms = $this->smsService->delete($command->id);
            return new DeleteSmsCommandResponse($sms);
        });
    }
}
