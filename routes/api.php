<?php

use App\Http\Controllers\Api\V1\SmsController;

// Bank SMS sent by a device, e.g. tools/mac-sms-sync (token from `php artisan hisabi:sms-sync-token`)
Route::middleware('auth:sanctum')->post('/v1/sms/sync', [SmsController::class, 'sync']);
