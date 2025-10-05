<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Budget;
use App\Domains\Transaction\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'budgets' => Budget::all(),
            'hasData' => (bool) Transaction::count()
        ]);
    }
}
