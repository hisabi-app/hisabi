<?php

namespace App\Http\Commands\Budget\UpdateBudgetCommand;

use App\Domains\Budget\Models\Budget;
use App\Http\Resources\BudgetResource;
use Illuminate\Http\JsonResponse;

readonly class UpdateBudgetCommandResponse
{
    public function __construct(
        private Budget $budget
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'budget' => new BudgetResource($this->budget),
        ]);
    }
}
