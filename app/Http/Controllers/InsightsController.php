<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Domains\Transaction\Models\Transaction;

class InsightsController extends Controller
{
    public function index()
    {
        return Inertia::render('Insights', [
            'hasData' => (bool) Transaction::count()
        ]);
    }
}
