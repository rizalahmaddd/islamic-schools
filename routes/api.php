<?php

use App\Http\Controllers\Api\OpenApiDocumentController;
use App\Http\Controllers\Api\V1\PaymentWebhookController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Rendered by Scalar at /docs/api (config/scalar.php).
Route::get('openapi.json', OpenApiDocumentController::class)->name('api.openapi');

// Private channel auth for realtime events (Reverb/Echo) with a mobile Bearer token: /api/broadcasting/auth.
Broadcast::routes(['middleware' => ['auth:sanctum']]);

// REST API for mobile and other clients. Each module file mirrors its web counterpart in routes/*.php.
Route::prefix('v1')->name('api.v1.')->middleware('throttle:api')->group(function () {
    require __DIR__.'/api/v1/auth.php';

    // Webhook Payment Gateway (TriPay)
    Route::post('payment/webhook', PaymentWebhookController::class)
        ->name('payment.webhook')
        ->middleware('feature:finance.transactions');

    Route::middleware('auth:sanctum')->group(function () {
        require __DIR__.'/api/v1/account.php';
        require __DIR__.'/api/v1/general.php';
        require __DIR__.'/api/v1/master-data.php';
    });
});
