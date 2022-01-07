<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index()
    {
        return Inertia::render('Transaction/Index', [
            'transactions' => Transaction::paginate(100)
        ]);
    }
}
