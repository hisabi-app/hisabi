<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class SmsController extends Controller
{
    public function index()
    {
        return Inertia::render('Sms/Index');
    }
}
