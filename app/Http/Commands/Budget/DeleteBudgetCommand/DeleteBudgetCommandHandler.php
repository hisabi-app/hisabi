<?php

namespace App\Http\Commands\Budget\DeleteBudgetCommand;

use App\Domains\Budget\Services\BudgetService;
use Illuminate\Support\Facades\DB;

class DeleteBudgetCommandHandler
{
    public function __construct(
        private readonly BudgetService $budgetService
    ) {}

    public function handle(DeleteBudgetCommand $command): DeleteBudgetCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $budget = $this->budgetService->delete($command->id);
            return new DeleteBudgetCommandResponse($budget);
        });
    }
}
