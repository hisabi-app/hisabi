<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Commands\Budget\CreateBudgetCommand\CreateBudgetCommand;
use App\Http\Commands\Budget\CreateBudgetCommand\CreateBudgetCommandHandler;
use App\Http\Commands\Budget\DeleteBudgetCommand\DeleteBudgetCommand;
use App\Http\Commands\Budget\DeleteBudgetCommand\DeleteBudgetCommandHandler;
use App\Http\Commands\Budget\UpdateBudgetCommand\UpdateBudgetCommand;
use App\Http\Commands\Budget\UpdateBudgetCommand\UpdateBudgetCommandHandler;
use App\Http\Controllers\Controller;
use App\Http\Queries\Budget\GetBudgetDetailQuery\GetBudgetDetailQuery;
use App\Http\Queries\Budget\GetBudgetDetailQuery\GetBudgetDetailQueryHandler;
use App\Http\Queries\Budget\GetBudgetsHistoryQuery\GetBudgetsHistoryQuery;
use App\Http\Queries\Budget\GetBudgetsHistoryQuery\GetBudgetsHistoryQueryHandler;
use App\Http\Queries\Budget\GetBudgetsQuery\GetBudgetsQuery;
use App\Http\Queries\Budget\GetBudgetsQuery\GetBudgetsQueryHandler;
use App\Http\Requests\Api\V1\CreateBudgetRequest;
use App\Http\Requests\Api\V1\GetBudgetsHistoryRequest;
use App\Http\Requests\Api\V1\UpdateBudgetRequest;
use Illuminate\Http\JsonResponse;

class BudgetController extends Controller
{
    public function __construct(
        private readonly GetBudgetsQueryHandler $getBudgetsQueryHandler,
        private readonly GetBudgetsHistoryQueryHandler $getBudgetsHistoryQueryHandler,
        private readonly GetBudgetDetailQueryHandler $getBudgetDetailQueryHandler,
        private readonly CreateBudgetCommandHandler $createBudgetCommandHandler,
        private readonly UpdateBudgetCommandHandler $updateBudgetCommandHandler,
        private readonly DeleteBudgetCommandHandler $deleteBudgetCommandHandler
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

    public function detail(int $id): JsonResponse
    {
        $query = new GetBudgetDetailQuery(id: $id);

        return $this->getBudgetDetailQueryHandler->handle($query)->toResponse();
    }

    public function store(CreateBudgetRequest $request): JsonResponse
    {
        $command = new CreateBudgetCommand(
            data: $request->validated()
        );

        return $this->createBudgetCommandHandler->handle($command)->toResponse();
    }

    public function update(UpdateBudgetRequest $request, int $id): JsonResponse
    {
        $command = new UpdateBudgetCommand(
            id: $id,
            data: $request->validated()
        );

        return $this->updateBudgetCommandHandler->handle($command)->toResponse();
    }

    public function destroy(int $id): JsonResponse
    {
        $command = new DeleteBudgetCommand(id: $id);

        return $this->deleteBudgetCommandHandler->handle($command)->toResponse();
    }
}
