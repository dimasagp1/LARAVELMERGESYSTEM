<?php

use App\Core\Controllers\ModuleFeatureController;
use App\Modules\Procurement\Controllers\BudgetManagementController;
use App\Modules\Procurement\Controllers\DeliveryLogisticsController;
use App\Modules\Procurement\Controllers\ItemStandardPriceController;
use App\Modules\Procurement\Controllers\PrBonController;
use App\Modules\Procurement\Controllers\PrItemWorkflowController;
use App\Modules\Procurement\Controllers\ProcurementController;
use App\Modules\Procurement\Controllers\PurchaseRequestController;
use App\Modules\Procurement\Controllers\QualityTargetController;
use Illuminate\Support\Facades\Route;

Route::prefix('procurement')
    ->name('procurement.')
    ->group(function () {
        // Overview Dashboard
        Route::get('/', [ProcurementController::class, 'index'])->name('index');

        // Purchase Requests
        Route::get('/purchase-requests', [PurchaseRequestController::class, 'index'])->name('purchase-requests.index');
        Route::get('/purchase-requests/create', [PurchaseRequestController::class, 'create'])->name('purchase-requests.create');
        Route::post('/purchase-requests', [PurchaseRequestController::class, 'store'])->name('purchase-requests.store');
        Route::get('/purchase-requests/{id}', [PurchaseRequestController::class, 'show'])->name('purchase-requests.show');
        Route::post('/purchase-requests/{id}/bulk-revise', [PurchaseRequestController::class, 'bulkRevise'])->name('purchase-requests.bulk-revise');
        Route::get('/purchase-requests/{id}/pdf', [PurchaseRequestController::class, 'exportPdf'])->name('purchase-requests.pdf');

        // Workflow & Estimates
        Route::get('/item-workflow', [ModuleFeatureController::class, 'render'])->name('item-workflow');
        Route::get('/estimates', [ModuleFeatureController::class, 'render'])->name('estimates');
        Route::get('/revisions', [ModuleFeatureController::class, 'render'])->name('revisions');

        // Granular 8-Step Item Workflow
        Route::post('/items/{id}/estimate', [PrItemWorkflowController::class, 'updateEstimates'])->name('items.estimate');
        Route::post('/items/{id}/approve-l1', [PrItemWorkflowController::class, 'approveL1'])->name('items.approve-l1');
        Route::post('/items/{id}/holding-quote', [PrItemWorkflowController::class, 'submitHoldingQuotation'])->name('items.holding-quote');
        Route::post('/items/{id}/actual-price', [PrItemWorkflowController::class, 'inputActualPrice'])->name('items.actual-price');
        Route::post('/items/{id}/approve-l2', [PrItemWorkflowController::class, 'approveL2'])->name('items.approve-l2');
        Route::post('/items/{id}/reject', [PrItemWorkflowController::class, 'reject'])->name('items.reject');
        Route::post('/items/{id}/process-po', [PrItemWorkflowController::class, 'processPo'])->name('items.process-po');
        Route::post('/items/{id}/send-note', [PrItemWorkflowController::class, 'sendNote'])->name('items.send-note');

        // PR Bon Sementara (Special Procurement)
        Route::get('/pr-bons', [PrBonController::class, 'index'])->name('pr-bons.index');
        Route::get('/pr-bons/staging', [PrBonController::class, 'staging'])->name('pr-bons.staging');
        Route::get('/pr-bons/approval', [ModuleFeatureController::class, 'render'])->name('pr-bons.approval');
        Route::post('/pr-bons', [PrBonController::class, 'store'])->name('pr-bons.store');
        Route::get('/pr-bons/{id}', [PrBonController::class, 'show'])->name('pr-bons.show');
        Route::post('/pr-bons/{id}/decision', [PrBonController::class, 'gmDecision'])->name('pr-bons.decision');
        Route::get('/pr-bons/{id}/print', [PrBonController::class, 'printPdf'])->name('pr-bons.print');

        // Purchase Order & Odoo Sync
        Route::get('/purchase-orders', [ModuleFeatureController::class, 'render'])->name('purchase-orders');
        Route::get('/odoo-sync', [ModuleFeatureController::class, 'render'])->name('odoo-sync');
        Route::get('/odoo-manual', [ModuleFeatureController::class, 'render'])->name('odoo-manual');

        // Logistik & Penerimaan Fisik Barang
        Route::get('/deliveries', [DeliveryLogisticsController::class, 'index'])->name('deliveries.index');
        Route::get('/deliveries/receipts', [ModuleFeatureController::class, 'render'])->name('deliveries.receipts');
        Route::get('/deliveries/holding', [ModuleFeatureController::class, 'render'])->name('deliveries.holding');
        Route::get('/deliveries/retur', [ModuleFeatureController::class, 'render'])->name('deliveries.retur');
        Route::post('/items/{id}/deliveries', [DeliveryLogisticsController::class, 'recordDelivery'])->name('deliveries.store');
        Route::post('/items/{id}/retur', [DeliveryLogisticsController::class, 'recordRetur'])->name('deliveries.retur.post');

        // Pagu Anggaran FAT
        Route::get('/budgets', [BudgetManagementController::class, 'index'])->name('budgets.index');
        Route::get('/staging-pagu', [ModuleFeatureController::class, 'render'])->name('staging-pagu');
        Route::get('/settings/finance-budget', [ModuleFeatureController::class, 'render'])->name('settings.finance-budget');
        Route::get('/budgets/bypass', [ModuleFeatureController::class, 'render'])->name('budgets.bypass');
        Route::post('/budgets', [BudgetManagementController::class, 'store'])->name('budgets.store');

        // Standar Harga & AVL
        Route::get('/standard-prices', [ItemStandardPriceController::class, 'index'])->name('standard-prices.index');
        Route::get('/vendors', [ModuleFeatureController::class, 'render'])->name('vendors');
        Route::get('/standard-prices/sync', [ModuleFeatureController::class, 'render'])->name('standard-prices.sync');
        Route::post('/standard-prices', [ItemStandardPriceController::class, 'store'])->name('standard-prices.store');
        Route::post('/standard-prices/sync-master', [ItemStandardPriceController::class, 'syncMaster'])->name('standard-prices.sync-master');

        // Sasaran Mutu 2026 & KPI
        Route::get('/sasaran-mutu', [QualityTargetController::class, 'index'])->name('sasaran-mutu.index');
        Route::get('/loss-time-logs', [ModuleFeatureController::class, 'render'])->name('loss-time-logs');
        Route::get('/hris-sync', [ModuleFeatureController::class, 'render'])->name('hris-sync');
        Route::post('/sasaran-mutu/loss-time', [QualityTargetController::class, 'storeLossTime'])->name('sasaran-mutu.loss-time');
        Route::get('/sasaran-mutu/pdf', [QualityTargetController::class, 'exportPdf'])->name('sasaran-mutu.pdf');

        // Master & Laporan
        Route::get('/reports', [ModuleFeatureController::class, 'render'])->name('reports');
        Route::get('/master-items', [ModuleFeatureController::class, 'render'])->name('master-items');
        Route::get('/settings', [ModuleFeatureController::class, 'render'])->name('settings');
    });
