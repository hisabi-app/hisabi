<?php

namespace App\Http\Commands\Budget\CreateBudgetCommand;

use App\Domains\Budget\Services\BudgetService;
use Illuminate\Support\Facades\DB;

class CreateBudgetCommandHandler
{
    public function __construct(
        private readonly BudgetService $budgetService
    ) {}

    public function handle(CreateBudgetCommand $command): CreateBudgetCommandResponse
    {
        return DB::transaction(function () use ($command) {
            $budget = $this->budgetService->create($command->data);
            return new CreateBudgetCommandResponse($budget);
        });
    }
}
