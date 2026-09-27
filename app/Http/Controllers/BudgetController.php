<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class BudgetController extends Controller
{
    public function index()
    {
        return Inertia::render('Budget/Index');
    }
}
