<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Commands\Sms\CreateSmsCommand\CreateSmsCommand;
use App\Http\Commands\Sms\CreateSmsCommand\CreateSmsCommandHandler;
use App\Http\Commands\Sms\UpdateSmsCommand\UpdateSmsCommand;
use App\Http\Commands\Sms\UpdateSmsCommand\UpdateSmsCommandHandler;
use App\Http\Queries\Sms\GetSmsQuery\GetSmsQuery;
use App\Http\Queries\Sms\GetSmsQuery\GetSmsQueryHandler;
use App\Http\Requests\Api\V1\CreateSmsRequest;
use App\Http\Requests\Api\V1\UpdateSmsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    public function __construct(
        private readonly GetSmsQueryHandler $getSmsQueryHandler,
        private readonly CreateSmsCommandHandler $createSmsCommandHandler,
        private readonly UpdateSmsCommandHandler $updateSmsCommandHandler
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetSmsQuery(
            perPage: $request->input('per_page', 100)
        );

        return $this->getSmsQueryHandler->handle($query)->toResponse();
    }

    public function store(CreateSmsRequest $request): JsonResponse
    {
        $command = new CreateSmsCommand(
            data: $request->validated()
        );

        return $this->createSmsCommandHandler->handle($command)->toResponse();
    }

    public function update(UpdateSmsRequest $request, int $id): JsonResponse
    {
        $command = new UpdateSmsCommand(
            id: $id,
            data: $request->validated()
        );

        return $this->updateSmsCommandHandler->handle($command)->toResponse();
    }
}

