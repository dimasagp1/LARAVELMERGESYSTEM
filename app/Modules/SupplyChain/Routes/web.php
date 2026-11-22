<?php

use App\Core\Controllers\ModuleFeatureController;
use App\Modules\SupplyChain\Controllers\BomMasterController;
use App\Modules\SupplyChain\Controllers\GoodsReceiptController;
use App\Modules\SupplyChain\Controllers\InventoryStockController;
use App\Modules\SupplyChain\Controllers\MaterialReturnController;
use App\Modules\SupplyChain\Controllers\MaterialUsageController;
use App\Modules\SupplyChain\Controllers\MrpController;
use App\Modules\SupplyChain\Controllers\ProductMasterController;
use App\Modules\SupplyChain\Controllers\QuarantineController;
use App\Modules\SupplyChain\Controllers\SupplyChainController;
use App\Modules\SupplyChain\Controllers\ThreeWayMatchingController;
use Illuminate\Support\Facades\Route;

Route::prefix('supply-chain')
    ->name('supply-chain.')
    ->group(function () {
        // Overview Interactive Dashboard
        Route::get('/', [SupplyChainController::class, 'index'])->name('index');

        // Master Data SCM
        Route::get('/products', [ProductMasterController::class, 'index'])->name('products');
        Route::post('/products', [ProductMasterController::class, 'store'])->name('products.store');
        Route::get('/products/export', [ProductMasterController::class, 'export'])->name('products.export');
        Route::put('/products/{product}', [ProductMasterController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductMasterController::class, 'destroy'])->name('products.destroy');

        Route::get('/boms', [BomMasterController::class, 'index'])->name('boms');
        Route::post('/boms', [BomMasterController::class, 'store'])->name('boms.store');
        Route::get('/boms/export', [BomMasterController::class, 'export'])->name('boms.export');
        Route::put('/boms/{bom}', [BomMasterController::class, 'update'])->name('boms.update');
        Route::delete('/boms/{bom}', [BomMasterController::class, 'destroy'])->name('boms.destroy');
        Route::get('/work-centers', [ModuleFeatureController::class, 'render'])->name('work-centers');
        Route::get('/vendors', [ModuleFeatureController::class, 'render'])->name('vendors');
        Route::get('/parameters', [ModuleFeatureController::class, 'render'])->name('parameters');

        // Perencanaan & PPIC
        Route::get('/sales-data', [ModuleFeatureController::class, 'render'])->name('sales-data');
        Route::get('/forecasts', [ModuleFeatureController::class, 'render'])->name('forecasts');
        Route::get('/mps', [ModuleFeatureController::class, 'render'])->name('mps');
        Route::get('/alerts', [ModuleFeatureController::class, 'render'])->name('alerts');

        // Kontrol Anggaran (Budget)
        Route::get('/budgets/revenue', [ModuleFeatureController::class, 'render'])->name('budgets.revenue');
        Route::get('/budgets/departments', [ModuleFeatureController::class, 'render'])->name('budgets.departments');
        Route::get('/cost-centers', [ModuleFeatureController::class, 'render'])->name('cost-centers');
        Route::get('/budgets/monitoring', [ModuleFeatureController::class, 'render'])->name('budgets.monitoring');

        // Pengadaan Terpadu
        Route::get('/procurement/operational-pr', [ModuleFeatureController::class, 'render'])->name('procurement.operational-pr');
        Route::get('/procurement/non-operational-pr', [ModuleFeatureController::class, 'render'])->name('procurement.non-operational-pr');
        Route::get('/procurement/estimates', [ModuleFeatureController::class, 'render'])->name('procurement.estimates');
        Route::get('/procurement/purchase-orders', [ModuleFeatureController::class, 'render'])->name('procurement.purchase-orders');

        // Material Requirements Planning (MRP)
        Route::get('/mrp', [MrpController::class, 'index'])->name('mrp.index');
        Route::post('/mrp/run', [MrpController::class, 'runMrp'])->name('mrp.run');
        Route::get('/mrp/{id}', [MrpController::class, 'show'])->name('mrp.show');
        Route::post('/mrp/{id}/convert-to-pr', [MrpController::class, 'convertToPr'])->name('mrp.convert-to-pr');

        // Inbound Goods Receipt (GRN) & Quarantine Entry
        Route::get('/goods-receipt', [GoodsReceiptController::class, 'index'])->name('goods-receipt.index');
        Route::get('/goods-receipt/create', [GoodsReceiptController::class, 'create'])->name('goods-receipt.create');
        Route::post('/goods-receipt', [GoodsReceiptController::class, 'store'])->name('goods-receipt.store');
        Route::get('/goods-receipt/{id}', [GoodsReceiptController::class, 'show'])->name('goods-receipt.show');
        Route::get('/goods-receipt/{id}/pdf', [GoodsReceiptController::class, 'exportPdf'])->name('goods-receipt.pdf');

        // QC Incoming Gatekeeper (4 CPOTB Parameters)
        Route::get('/quarantine', [QuarantineController::class, 'index'])->name('quarantine.index');
        Route::post('/quarantine/{id}/inspect', [QuarantineController::class, 'inspect'])->name('quarantine.inspect');

        // Multi-Warehouse Stock Matrix & Movement Ledger
        Route::get('/stock', [InventoryStockController::class, 'index'])->name('stock.index');

        // Material Usage / Bukti Serah Terima Bahan (BSTB)
        Route::get('/material-usage', [MaterialUsageController::class, 'index'])->name('material-usage.index');
        Route::get('/material-usage/create', [MaterialUsageController::class, 'create'])->name('material-usage.create');
        Route::post('/material-usage', [MaterialUsageController::class, 'store'])->name('material-usage.store');
        Route::get('/material-usage/{id}', [MaterialUsageController::class, 'show'])->name('material-usage.show');
        Route::get('/material-usage/{id}/pdf', [MaterialUsageController::class, 'exportPdf'])->name('material-usage.pdf');

        // Material Return (Vendor Reject Memo)
        Route::get('/material-return', [MaterialReturnController::class, 'index'])->name('material-return.index');
        Route::get('/material-return/create', [MaterialReturnController::class, 'create'])->name('material-return.create');
        Route::post('/material-return', [MaterialReturnController::class, 'store'])->name('material-return.store');
        Route::get('/material-return/{id}/pdf', [MaterialReturnController::class, 'exportPdf'])->name('material-return.pdf');

        // Pergudangan tambahan
        Route::get('/stock-opname', [ModuleFeatureController::class, 'render'])->name('stock-opname');
        Route::get('/warehouse-scrap', [ModuleFeatureController::class, 'render'])->name('warehouse-scrap');

        // Manufaktur & Mutu
        Route::get('/production/work-orders', [ModuleFeatureController::class, 'render'])->name('production.work-orders');
        Route::get('/quality/inspections', [ModuleFeatureController::class, 'render'])->name('quality.inspections');
        Route::get('/quality/releases', [ModuleFeatureController::class, 'render'])->name('quality.releases');

        // 3-Way Matching AP Audit & Vendor Bills
        Route::get('/matching', [ThreeWayMatchingController::class, 'index'])->name('matching.index');
        Route::post('/matching', [ThreeWayMatchingController::class, 'match'])->name('matching.match');
        Route::post('/matching/{id}/bill', [ThreeWayMatchingController::class, 'createBill'])->name('matching.create-bill');
        Route::get('/finance/vouchers', [ModuleFeatureController::class, 'render'])->name('finance.vouchers');
        Route::get('/finance/cogs', [ModuleFeatureController::class, 'render'])->name('finance.cogs');
    });
