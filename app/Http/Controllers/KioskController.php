<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class KioskController extends Controller
{
    public function index()
    {
        return Inertia::render('Kiosk');
    }
}
