<?php

namespace App\Http\Controllers;

use App\Domains\Budget\Services\BudgetService;
use Inertia\Inertia;

class BudgetController extends Controller
{
    public function index(BudgetService $budgetService)
    {
        $firstTransactionDate = $budgetService->getFirstTransactionDate();

        return Inertia::render('Budget/Index', [
            // History only has something to compare once there's more than a month of transactions
            'hasHistory' => $firstTransactionDate !== null && $firstTransactionDate->lt(now()->subMonth()),
        ]);
    }
}
