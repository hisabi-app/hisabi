<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Commands\Home\DismissSuggestionCommand\DismissSuggestionCommand;
use App\Http\Commands\Home\DismissSuggestionCommand\DismissSuggestionCommandHandler;
use App\Http\Controllers\Controller;
use App\Http\Queries\Home\GetHomeQuery\GetHomeQuery;
use App\Http\Queries\Home\GetHomeQuery\GetHomeQueryHandler;
use App\Http\Requests\Api\V1\DismissSuggestionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct(
        private readonly GetHomeQueryHandler $getHomeQueryHandler,
        private readonly DismissSuggestionCommandHandler $dismissSuggestionCommandHandler
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = new GetHomeQuery(user: $request->user());

        return $this->getHomeQueryHandler->handle($query)->toResponse();
    }

    public function dismiss(DismissSuggestionRequest $request): JsonResponse
    {
        $command = new DismissSuggestionCommand(
            user: $request->user(),
            key: $request->validated('key')
        );

        return $this->dismissSuggestionCommandHandler->handle($command)->toResponse();
    }
}
