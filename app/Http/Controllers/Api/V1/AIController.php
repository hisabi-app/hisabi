<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Commands\AI\ChatCommand\ChatCommand;
use App\Http\Commands\AI\ChatCommand\ChatCommandHandler;
use App\Http\Requests\Api\V1\AIChatRequest;
use Illuminate\Http\JsonResponse;

class AIController extends Controller
{
    public function __construct(
        private readonly ChatCommandHandler $chatCommandHandler
    ) {}

    public function chat(AIChatRequest $request): JsonResponse
    {
        $command = new ChatCommand(
            messages: $request->validated()['messages']
        );

        return $this->chatCommandHandler->handle($command)->toResponse();
    }
}
