<?php

namespace App\Http\Commands\Sms\CreateSmsCommand;

use App\Domains\Sms\Services\SmsService;
use Illuminate\Support\Facades\DB;

class CreateSmsCommandHandler
{
    public function __construct(
        private readonly SmsService $smsService
    ) {}

    public function handle(CreateSmsCommand $command): CreateSmsCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $smsCollection = $this->smsService->create($command->data);
            return new CreateSmsCommandResponse($smsCollection);
        });
    }
}

