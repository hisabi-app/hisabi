<?php

use App\Models\Brand;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});


Route::get('/brand/create', function (Request $request) {
    return Brand::get();
});

Route::get('/brands', function (Request $request) {
    return Brand::get();
});

Route::get('/categories', function (Request $request) {
    return Category::get();
});

Route::get('/transactions', function (Request $request) {
    return Transaction::with('brand')->get()->transform(function ($transaction) {
        return [
            'id' => $transaction->id,
            'amount' => $transaction->amount,
            'brand' => $transaction->brand->name,
            'created_at' => $transaction->created_at,
            'updated_at' => $transaction->updated_at,
        ];
    });
});
