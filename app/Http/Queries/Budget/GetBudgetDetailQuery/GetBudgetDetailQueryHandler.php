<?php

namespace App\Http\Queries\Budget\GetBudgetDetailQuery;

use App\Domains\Budget\Models\Budget;
use App\Domains\Home\Services\HomeService;

class GetBudgetDetailQueryHandler
{
    public function __construct(
        private readonly HomeService $homeService
    ) {}

    public function handle(GetBudgetDetailQuery $query): GetBudgetDetailQueryResponse
    {
        return new GetBudgetDetailQueryResponse(
            $this->homeService->getBudgetDetail(Budget::findOrFail($query->id))
        );
    }
}
