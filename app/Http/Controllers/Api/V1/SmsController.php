<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Commands\Sms\CreateSmsCommand\CreateSmsCommand;
use App\Http\Commands\Sms\CreateSmsCommand\CreateSmsCommandHandler;
use App\Http\Requests\Api\V1\CreateSmsRequest;
use Illuminate\Http\JsonResponse;

class SmsController extends Controller
{
    public function __construct(
        private readonly CreateSmsCommandHandler $createSmsCommandHandler
    ) {}

    public function store(CreateSmsRequest $request): JsonResponse
    {
        $command = new CreateSmsCommand(
            data: $request->validated()
        );

        return $this->createSmsCommandHandler->handle($command)->toResponse();
    }
}

