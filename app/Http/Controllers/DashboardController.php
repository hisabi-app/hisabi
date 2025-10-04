<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Domains\Transaction\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        return Inertia::render('Dashboard', [
            'hasData' => (bool) Transaction::count()
        ]);
    }
}
