<?php

use Illuminate\Http\Request;
use App\Contracts\ReportManager;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

Route::redirect('/', '/dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions');
    Route::get('/brands', [BrandController::class, 'index'])->name('brands');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
    Route::get('/sms', [SmsController::class, 'index'])->name('sms');
    Route::get('/report', function(Request $request) {
        $start_date = $request->query('start_date');
        $end_date = $request->query('end_date');

        $data = [
            'sections' => app(ReportManager::class)->generate($start_date, $end_date),
            'currency' => config('hisabi.currency'),
            'range' => $start_date && $end_date ? $start_date . ' - ' . $end_date : now()->format('F Y')
        ];

        return view('report', $data);
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});
