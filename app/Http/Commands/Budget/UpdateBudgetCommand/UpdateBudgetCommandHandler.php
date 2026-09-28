<?php

namespace App\Http\Commands\Budget\UpdateBudgetCommand;

use App\Domains\Budget\Services\BudgetService;
use Illuminate\Support\Facades\DB;

class UpdateBudgetCommandHandler
{
    public function __construct(
        private readonly BudgetService $budgetService
    ) {}

    public function handle(UpdateBudgetCommand $command): UpdateBudgetCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $budget = $this->budgetService->update($command->id, $command->data);
            return new UpdateBudgetCommandResponse($budget);
        });
    }
}
