<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Queries\Budget\GetBudgetDailySpendingQuery\GetBudgetDailySpendingQuery;
use App\Http\Queries\Budget\GetBudgetDailySpendingQuery\GetBudgetDailySpendingQueryHandler;
use App\Http\Queries\Budget\GetBudgetsQuery\GetBudgetsQuery;
use App\Http\Queries\Budget\GetBudgetsQuery\GetBudgetsQueryHandler;
use Illuminate\Http\JsonResponse;

class BudgetController extends Controller
{
    public function __construct(
        private readonly GetBudgetsQueryHandler $getBudgetsQueryHandler,
        private readonly GetBudgetDailySpendingQueryHandler $getBudgetDailySpendingQueryHandler
    ) {}

    public function index(): JsonResponse
    {
        $query = new GetBudgetsQuery();

        return $this->getBudgetsQueryHandler->handle($query)->toResponse();
    }

    public function dailySpending(int $id): JsonResponse
    {
        $query = new GetBudgetDailySpendingQuery($id);

        return $this->getBudgetDailySpendingQueryHandler->handle($query)->toResponse();
    }
}
