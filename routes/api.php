<?php

use App\Http\Controllers\WebhookController;
use App\Http\Middleware\VerifyLynkSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
*/

// Lynk Payment Webhook
Route::post('/webhooks/lynk-payment', [WebhookController::class, 'handleLynkPayment'])
    ->middleware(VerifyLynkSignature::class)
    ->name('webhooks.lynk-payment');
