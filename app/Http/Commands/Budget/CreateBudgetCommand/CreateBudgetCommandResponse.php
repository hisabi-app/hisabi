<?php

namespace App\Http\Commands\Budget\CreateBudgetCommand;

use App\Domains\Budget\Models\Budget;
use App\Http\Resources\BudgetResource;
use Illuminate\Http\JsonResponse;

readonly class CreateBudgetCommandResponse
{
    public function __construct(
        private Budget $budget
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'budget' => new BudgetResource($this->budget),
        ], 201);
    }
}
