<?php

namespace App\Http\Commands\Budget\DeleteBudgetCommand;

use App\Domains\Budget\Models\Budget;
use Illuminate\Http\JsonResponse;

readonly class DeleteBudgetCommandResponse
{
    public function __construct(
        private Budget $budget
    ) {}

    public function toResponse(): JsonResponse
    {
        return response()->json([
            // Plain fields only: the appended spending attributes would query a budget that no longer exists
            'budget' => $this->budget->only(['id', 'name']),
        ]);
    }
}
