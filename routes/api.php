<?php

use App\Http\Controllers\Api\V1\SystemApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - HerbaTech ERP Integration Suite
|--------------------------------------------------------------------------
|
| Versioned REST API endpoints for external systems (WMS, IoT, Odoo, SAP,
| biometric attendance devices, and third-party logistics).
|
*/

Route::prefix('v1')->group(function () {
    // 1. System & Branding Config (Public)
    Route::get('/system/settings', [SystemApiController::class, 'settings'])->name('api.v1.settings');

    // 2. Active Modules & Navigation
    Route::get('/modules', [SystemApiController::class, 'modules'])->name('api.v1.modules');

    // 3. User Directory
    Route::get('/users', [SystemApiController::class, 'users'])->name('api.v1.users');

    // 4. Master Data & Operational Integration
    Route::get('/products', [SystemApiController::class, 'products'])->name('api.v1.products');
    Route::get('/inventory', [SystemApiController::class, 'inventory'])->name('api.v1.inventory');

    // 5. Inbound Webhook Receiver
    Route::post('/webhooks/inbound', [SystemApiController::class, 'handleWebhook'])->name('api.v1.webhook');

    // 6. Authenticated User Profile
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'user' => $request->user()->load('department'),
        ]);
    });
});
