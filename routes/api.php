<?php

use App\Http\Controllers\Api\V1\SmsController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

Route::post('/login', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    return $user->createToken($request->device_name)->plainTextToken;
});

// Bank SMS sent by a device, e.g. tools/mac-sms-sync (token from `php artisan hisabi:sms-sync-token`)
Route::middleware('auth:sanctum')->post('/v1/sms/sync', [SmsController::class, 'sync']);
