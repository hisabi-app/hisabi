<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Queries\Budget\GetBudgetsQuery\GetBudgetsQuery;
use App\Http\Queries\Budget\GetBudgetsQuery\GetBudgetsQueryHandler;
use Illuminate\Http\JsonResponse;

class BudgetController extends Controller
{
    public function __construct(
        private readonly GetBudgetsQueryHandler $getBudgetsQueryHandler
    ) {}

    public function index(): JsonResponse
    {
        $query = new GetBudgetsQuery();

        return $this->getBudgetsQueryHandler->handle($query)->toResponse();
    }
}
