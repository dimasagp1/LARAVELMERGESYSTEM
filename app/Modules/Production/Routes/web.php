<?php

use App\Core\Controllers\ModuleFeatureController;
use App\Modules\Production\Controllers\DailyReportExecutionController;
use App\Modules\Production\Controllers\MachineMasterController;
use App\Modules\Production\Controllers\OeeMonitoringController;
use App\Modules\Production\Controllers\ProductionController;
use App\Modules\Production\Controllers\RejectScrapController;
use App\Modules\Production\Controllers\WpsScheduleController;
use App\Modules\SupplyChain\Controllers\BomMasterController;
use App\Modules\SupplyChain\Controllers\ProductMasterController;
use Illuminate\Support\Facades\Route;

// Production & Manufacturing Module Routes (/production)
Route::prefix('production')->name('production.')->group(function () {
    // 1. Overview Dashboard & Analytics
    Route::get('/', [ProductionController::class, 'index'])->name('overview');
    Route::get('/index', [ProductionController::class, 'index'])->name('index');
    Route::get('/analytics/heat-grid', [ModuleFeatureController::class, 'render'])->name('analytics.heat-grid');
    Route::get('/analytics/pareto', [ModuleFeatureController::class, 'render'])->name('analytics.pareto');
    Route::get('/analytics/rejects', [ModuleFeatureController::class, 'render'])->name('analytics.rejects');

    // 2. Master Data & Konfigurasi
    Route::get('/master/products', [ProductMasterController::class, 'index'])->name('master.products');
    Route::get('/master/machines', [MachineMasterController::class, 'index'])->name('master.machines');
    Route::post('/master/machines', [MachineMasterController::class, 'storeMachine'])->name('master.machines.store');
    Route::put('/master/machines/{machine}', [MachineMasterController::class, 'updateMachine'])->name('master.machines.update');
    Route::delete('/master/machines/{machine}', [MachineMasterController::class, 'destroyMachine'])->name('master.machines.destroy');
    Route::get('/master/machines/export', [MachineMasterController::class, 'export'])->name('master.machines.export');

    Route::get('/master/lines', [MachineMasterController::class, 'index'])->name('master.lines');
    Route::post('/master/lines', [MachineMasterController::class, 'storeLine'])->name('master.lines.store');
    Route::put('/master/lines/{line}', [MachineMasterController::class, 'updateLine'])->name('master.lines.update');
    Route::delete('/master/lines/{line}', [MachineMasterController::class, 'destroyLine'])->name('master.lines.destroy');

    Route::get('/master/downtime-reasons', [ModuleFeatureController::class, 'render'])->name('master.downtime-reasons');
    Route::get('/master/settings', [ModuleFeatureController::class, 'render'])->name('master.settings');
    Route::get('/master/history', [ModuleFeatureController::class, 'render'])->name('master.history');

    // 3. Weekly Production Schedule (WPS) - PPIC
    Route::prefix('schedules')->name('schedules.')->group(function () {
        Route::get('/', [WpsScheduleController::class, 'index'])->name('index');
        Route::get('/create', [WpsScheduleController::class, 'create'])->name('create');
        Route::post('/', [WpsScheduleController::class, 'store'])->name('store');
        Route::get('/odoo-sync', [ModuleFeatureController::class, 'render'])->name('odoo-sync');
        Route::post('/odoo-sync', [WpsScheduleController::class, 'syncOdoo'])->name('odoo-sync.post');
        Route::get('/packing-hold', [ModuleFeatureController::class, 'render'])->name('packing-hold');
        Route::post('/{id}/toggle-hold', [WpsScheduleController::class, 'toggleHold'])->name('toggle-hold');
        Route::post('/sync-odoo', [WpsScheduleController::class, 'syncOdoo'])->name('sync-odoo');
        Route::get('/{id}/pdf', [WpsScheduleController::class, 'exportPdf'])->name('pdf');
    });

    // 4. Laporan Harian Produksi (LHP) & Live Console Timer
    Route::get('/timer-console', [ModuleFeatureController::class, 'render'])->name('timer-console');
    Route::prefix('daily-reports')->name('daily-reports.')->group(function () {
        Route::get('/', [DailyReportExecutionController::class, 'index'])->name('index');
        Route::get('/create', [DailyReportExecutionController::class, 'create'])->name('create');
        Route::post('/', [DailyReportExecutionController::class, 'store'])->name('store');
        Route::get('/approvals', [ModuleFeatureController::class, 'render'])->name('approvals');
        Route::get('/{id}', [DailyReportExecutionController::class, 'show'])->name('show');
        Route::post('/{id}/start', [DailyReportExecutionController::class, 'start'])->name('start');
        Route::post('/{id}/pause', [DailyReportExecutionController::class, 'pause'])->name('pause');
        Route::post('/{id}/resume', [DailyReportExecutionController::class, 'resume'])->name('resume');
        Route::post('/{id}/end', [DailyReportExecutionController::class, 'end'])->name('end');
        Route::post('/{id}/submit', [DailyReportExecutionController::class, 'submit'])->name('submit');
        Route::post('/{id}/lock', [DailyReportExecutionController::class, 'lock'])->name('lock');
        Route::post('/{id}/spawn-deviation', [DailyReportExecutionController::class, 'spawnDeviation'])->name('spawn-deviation');
        Route::get('/{id}/pdf', [DailyReportExecutionController::class, 'exportPdf'])->name('pdf');
    });

    // Alias batch routes for batch-centric tracking
    Route::get('/batches', [DailyReportExecutionController::class, 'index'])->name('batches.index');

    // 5. OEE & Downtime Pareto Monitoring
    Route::get('/oee', [OeeMonitoringController::class, 'index'])->name('oee.index');

    // 6. Reject & Scrap Management
    Route::prefix('scrap')->name('scrap.')->group(function () {
        Route::get('/', [RejectScrapController::class, 'index'])->name('index');
        Route::post('/', [RejectScrapController::class, 'store'])->name('store');
        Route::get('/breakdown', [ModuleFeatureController::class, 'render'])->name('breakdown');
        Route::get('/odoo-sync', [ModuleFeatureController::class, 'render'])->name('odoo-sync');
        Route::get('/warehouse', [ModuleFeatureController::class, 'render'])->name('warehouse');
    });

    // 7. SCM & Persediaan Pabrik
    Route::get('/boms', [BomMasterController::class, 'index'])->name('boms');
    Route::get('/stocks', [ModuleFeatureController::class, 'render'])->name('stocks');
    Route::get('/material-usage', [ModuleFeatureController::class, 'render'])->name('material-usage');
    Route::get('/stock-opname', [ModuleFeatureController::class, 'render'])->name('stock-opname');
    Route::get('/stock-opname/stelling', [ModuleFeatureController::class, 'render'])->name('stock-opname.stelling');

    // 8. Pengiriman & Logistik Fleet
    Route::get('/deliveries', [ModuleFeatureController::class, 'render'])->name('deliveries');
    Route::get('/deliveries/stock-check', [ModuleFeatureController::class, 'render'])->name('deliveries.stock-check');
    Route::get('/deliveries/fleet', [ModuleFeatureController::class, 'render'])->name('deliveries.fleet');
    Route::get('/deliveries/documents', [ModuleFeatureController::class, 'render'])->name('deliveries.documents');
    Route::get('/deliveries/kpi', [ModuleFeatureController::class, 'render'])->name('deliveries.kpi');

    // 9. Integrasi Eksternal
    Route::get('/integrations/odoo', [ModuleFeatureController::class, 'render'])->name('integrations.odoo');
    Route::get('/integrations/hris', [ModuleFeatureController::class, 'render'])->name('integrations.hris');
    Route::get('/integrations/finance', [ModuleFeatureController::class, 'render'])->name('integrations.finance');
});

// Alias route /produksi to /production
Route::prefix('produksi')->group(function () {
    Route::get('/', [ProductionController::class, 'index']);
    Route::get('/schedules', [WpsScheduleController::class, 'index']);
    Route::get('/daily-reports', [DailyReportExecutionController::class, 'index']);
    Route::get('/oee', [OeeMonitoringController::class, 'index']);
    Route::get('/scrap', [RejectScrapController::class, 'index']);
});
