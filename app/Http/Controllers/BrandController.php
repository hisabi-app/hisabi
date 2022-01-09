<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class BrandController extends Controller
{
    public function index()
    {
        return Inertia::render('Brand/Index');
    }
}
