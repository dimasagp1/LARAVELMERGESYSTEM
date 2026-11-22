<?php

use App\Core\Controllers\ModuleFeatureController;
use App\Modules\GeneralAffair\Controllers\AtkRequestController;
use App\Modules\GeneralAffair\Controllers\FixedAssetController;
use App\Modules\GeneralAffair\Controllers\GeneralAffairController;
use App\Modules\GeneralAffair\Controllers\PatrolController;
use App\Modules\GeneralAffair\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('general-affair')
    ->name('general-affair.')
    ->group(function () {
        Route::get('/', [GeneralAffairController::class, 'index'])->name('index');

        // Work Orders (FPU)
        Route::prefix('work-orders')->name('work-orders.')->group(function () {
            Route::get('/', [WorkOrderController::class, 'index'])->name('index');
            Route::get('/create', [WorkOrderController::class, 'create'])->name('create');
            Route::post('/', [WorkOrderController::class, 'store'])->name('store');
            Route::get('/inspection', [ModuleFeatureController::class, 'render'])->name('inspection');
            Route::get('/fpu-materials', [ModuleFeatureController::class, 'render'])->name('fpu-materials');
            Route::get('/progress', [ModuleFeatureController::class, 'render'])->name('progress');
            Route::get('/handover', [ModuleFeatureController::class, 'render'])->name('handover');
            Route::get('/print', [ModuleFeatureController::class, 'render'])->name('print');
            Route::get('/{id}', [WorkOrderController::class, 'show'])->name('show');
            Route::post('/{id}/assign', [WorkOrderController::class, 'assign'])->name('assign');
            Route::post('/{id}/check', [WorkOrderController::class, 'check'])->name('check');
            Route::post('/{id}/progress', [WorkOrderController::class, 'progress'])->name('progress.update');
            Route::post('/fpu/{id}/approve', [WorkOrderController::class, 'approveFpu'])->name('fpu.approve');
            Route::post('/{id}/handover', [WorkOrderController::class, 'handover'])->name('handover.submit');
            Route::post('/{id}/confirm-handover', [WorkOrderController::class, 'confirmHandover'])->name('confirm-handover');
            Route::get('/{id}/pdf', [WorkOrderController::class, 'printPdf'])->name('pdf');
        });

        // ATK & Rumah Tangga (RT)
        Route::prefix('atk')->name('atk.')->group(function () {
            Route::get('/', [AtkRequestController::class, 'index'])->name('index');
            Route::get('/create', [AtkRequestController::class, 'create'])->name('create');
            Route::post('/', [AtkRequestController::class, 'store'])->name('store');
            Route::get('/approvals', [ModuleFeatureController::class, 'render'])->name('approvals');
            Route::get('/revisions', [ModuleFeatureController::class, 'render'])->name('revisions');
            Route::get('/distribution', [ModuleFeatureController::class, 'render'])->name('distribution');
            Route::get('/reports', [ModuleFeatureController::class, 'render'])->name('reports');
            Route::get('/{id}', [AtkRequestController::class, 'show'])->name('show');
            Route::post('/{id}/approve-manager', [AtkRequestController::class, 'approveManager'])->name('approve-manager');
            Route::post('/{id}/approve-admin', [AtkRequestController::class, 'approveAdmin'])->name('approve-admin');
            Route::post('/{id}/revision', [AtkRequestController::class, 'revision'])->name('revision');
            Route::post('/{id}/handover', [AtkRequestController::class, 'handover'])->name('handover');
            Route::get('/{id}/pdf', [AtkRequestController::class, 'printPdf'])->name('pdf');
        });

        // Fixed Assets & DIR
        Route::prefix('assets')->name('assets.')->group(function () {
            Route::get('/', [FixedAssetController::class, 'index'])->name('index');
            Route::get('/create', [FixedAssetController::class, 'create'])->name('create');
            Route::post('/', [FixedAssetController::class, 'store'])->name('store');
            Route::get('/depreciation', [ModuleFeatureController::class, 'render'])->name('depreciation');
            Route::get('/mutations', [ModuleFeatureController::class, 'render'])->name('mutations');
            Route::get('/disposals', [ModuleFeatureController::class, 'render'])->name('disposals');
            Route::get('/dir-rooms', [ModuleFeatureController::class, 'render'])->name('dir-rooms');
            Route::get('/qr-scanner', [ModuleFeatureController::class, 'render'])->name('qr-scanner');
            Route::get('/ai-assistant', [ModuleFeatureController::class, 'render'])->name('ai-assistant');
            Route::get('/dir', [FixedAssetController::class, 'dir'])->name('dir');
            Route::get('/dir/{locationId}/pdf', [FixedAssetController::class, 'printDirPdf'])->name('dir.pdf');
            Route::get('/{id}', [FixedAssetController::class, 'show'])->name('show');
            Route::post('/{id}/mutate', [FixedAssetController::class, 'mutate'])->name('mutate');
            Route::post('/{id}/dispose', [FixedAssetController::class, 'dispose'])->name('dispose');
        });

        // Patrols (Security & Cleaning)
        Route::prefix('patrols')->name('patrols.')->group(function () {
            Route::get('/security', [PatrolController::class, 'securityIndex'])->name('security');
            Route::get('/security/checkpoints', [ModuleFeatureController::class, 'render'])->name('security.checkpoints');
            Route::get('/security/schedules', [ModuleFeatureController::class, 'render'])->name('security.schedules');
            Route::get('/security/scan', [ModuleFeatureController::class, 'render'])->name('security.scan');
            Route::post('/security/scan', [PatrolController::class, 'securityScan'])->name('security.scan.post');
            Route::get('/security/incidents', [ModuleFeatureController::class, 'render'])->name('security.incidents');
            Route::get('/security/reports', [ModuleFeatureController::class, 'render'])->name('security.reports');

            Route::get('/cleaning', [PatrolController::class, 'cleaningIndex'])->name('cleaning');
            Route::get('/cleaning/checkpoints', [ModuleFeatureController::class, 'render'])->name('cleaning.checkpoints');
            Route::get('/cleaning/schedules', [ModuleFeatureController::class, 'render'])->name('cleaning.schedules');
            Route::get('/cleaning/scan', [ModuleFeatureController::class, 'render'])->name('cleaning.scan');
            Route::post('/cleaning/scan', [PatrolController::class, 'cleaningScan'])->name('cleaning.scan.post');
            Route::get('/cleaning/reports', [ModuleFeatureController::class, 'render'])->name('cleaning.reports');
        });

        // IT Helpdesk & SLA
        Route::prefix('helpdesk')->name('helpdesk.')->group(function () {
            Route::get('/', [ModuleFeatureController::class, 'render'])->name('index');
            Route::get('/projects', [ModuleFeatureController::class, 'render'])->name('projects');
            Route::get('/tickets', [ModuleFeatureController::class, 'render'])->name('tickets');
            Route::get('/queues', [ModuleFeatureController::class, 'render'])->name('queues');
            Route::get('/sla-monitoring', [ModuleFeatureController::class, 'render'])->name('sla-monitoring');
            Route::get('/reports', [ModuleFeatureController::class, 'render'])->name('reports');
        });

        // Administrasi & Utilitas
        Route::get('/daily-logs', [ModuleFeatureController::class, 'render'])->name('daily-logs');
        Route::get('/chat', [ModuleFeatureController::class, 'render'])->name('chat');
        Route::get('/master-items', [ModuleFeatureController::class, 'render'])->name('master-items');
        Route::get('/admin/users', [ModuleFeatureController::class, 'render'])->name('admin.users');
        Route::get('/admin/activity-logs', [ModuleFeatureController::class, 'render'])->name('admin.activity-logs');
    });
