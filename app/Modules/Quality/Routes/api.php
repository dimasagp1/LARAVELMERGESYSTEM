<?php

use App\Modules\Quality\Controllers\QualityApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('quality')
    ->middleware(['api', 'auth:sanctum'])
    ->group(function () {
        Route::get('/metrics', [QualityApiController::class, 'metrics']);
        Route::post('/signature', [QualityApiController::class, 'saveSignature']);
        Route::get('/fishbone-suggestions', [QualityApiController::class, 'fishboneSuggestions']);
    });
