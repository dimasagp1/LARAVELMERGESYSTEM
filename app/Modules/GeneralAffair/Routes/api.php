<?php

use App\Modules\GeneralAffair\Controllers\GeneralAffairApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('general-affair')->name('api.general-affair.')->group(function () {
    Route::get('/metrics', [GeneralAffairApiController::class, 'metrics'])->name('metrics');
    Route::get('/checkpoints/verify', [GeneralAffairApiController::class, 'verifyQr'])->name('checkpoints.verify');
    Route::post('/security/scan', [GeneralAffairApiController::class, 'scanSecurity'])->name('security.scan');
    Route::post('/cleaning/scan', [GeneralAffairApiController::class, 'scanCleaning'])->name('cleaning.scan');
});
