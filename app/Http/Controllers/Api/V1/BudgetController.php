<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Commands\Budget\UpdateBudgetCommand\UpdateBudgetCommand;
use App\Http\Commands\Budget\UpdateBudgetCommand\UpdateBudgetCommandHandler;
use App\Http\Controllers\Controller;
use App\Http\Queries\Budget\GetBudgetsHistoryQuery\GetBudgetsHistoryQuery;
use App\Http\Queries\Budget\GetBudgetsHistoryQuery\GetBudgetsHistoryQueryHandler;
use App\Http\Queries\Budget\GetBudgetsQuery\GetBudgetsQuery;
use App\Http\Queries\Budget\GetBudgetsQuery\GetBudgetsQueryHandler;
use App\Http\Requests\Api\V1\GetBudgetsHistoryRequest;
use App\Http\Requests\Api\V1\UpdateBudgetRequest;
use Illuminate\Http\JsonResponse;

class BudgetController extends Controller
{
    public function __construct(
        private readonly GetBudgetsQueryHandler $getBudgetsQueryHandler,
        private readonly GetBudgetsHistoryQueryHandler $getBudgetsHistoryQueryHandler,
        private readonly UpdateBudgetCommandHandler $updateBudgetCommandHandler
    ) {}

    public function index(): JsonResponse
    {
        $query = new GetBudgetsQuery();

        return $this->getBudgetsQueryHandler->handle($query)->toResponse();
    }

    public function history(GetBudgetsHistoryRequest $request): JsonResponse
    {
        $query = new GetBudgetsHistoryQuery(
            from: $request->validated('from'),
            to: $request->validated('to')
        );

        return $this->getBudgetsHistoryQueryHandler->handle($query)->toResponse();
    }

    public function update(UpdateBudgetRequest $request, int $id): JsonResponse
    {
        $command = new UpdateBudgetCommand(
            id: $id,
            data: $request->validated()
        );

        return $this->updateBudgetCommandHandler->handle($command)->toResponse();
    }
}
