<?php

use App\Core\Controllers\ModuleFeatureController;
use App\Modules\Finance\Controllers\AdjJournalController;
use App\Modules\Finance\Controllers\BudgetPnLController;
use App\Modules\Finance\Controllers\CoaController;
use App\Modules\Finance\Controllers\FinanceController;
use App\Modules\Finance\Controllers\FinancialRatioController;
use App\Modules\Finance\Controllers\FinancialStatementController;
use App\Modules\Finance\Controllers\ReconciliationController;
use App\Modules\Finance\Controllers\TrialBalanceController;
use Illuminate\Support\Facades\Route;

Route::prefix('finance')
    ->name('finance.')
    ->group(function () {
        // Overview Dashboard
        Route::get('/', [FinanceController::class, 'index'])->name('index');
        Route::get('/manager-dashboard', [ModuleFeatureController::class, 'render'])->name('manager-dashboard');
        Route::get('/dept-dashboard', [ModuleFeatureController::class, 'render'])->name('dept-dashboard');

        // Master Chart of Accounts (COA / "Cost Of Account")
        Route::get('/coa', [CoaController::class, 'index'])->name('coa.index');
        Route::post('/coa', [CoaController::class, 'store'])->name('coa.store');
        Route::put('/coa/{coa}', [CoaController::class, 'update'])->name('coa.update');
        Route::delete('/coa/{coa}', [CoaController::class, 'destroy'])->name('coa.destroy');
        Route::post('/coa/{coa}/toggle-status', [CoaController::class, 'toggleStatus'])->name('coa.toggle-status');
        Route::get('/coa/export', [CoaController::class, 'export'])->name('coa.export');
        Route::get('/coa-mapping', [CoaController::class, 'index'])->name('coa-mapping');

        // Adjusting Journal Entries (AJE) Maker-Checker
        Route::get('/aje', [AdjJournalController::class, 'index'])->name('aje.index');
        Route::get('/aje/create', [AdjJournalController::class, 'create'])->name('aje.create');
        Route::post('/aje', [AdjJournalController::class, 'store'])->name('aje.store');
        Route::get('/aje/{id}', [AdjJournalController::class, 'show'])->name('aje.show');
        Route::post('/aje/{id}/submit', [AdjJournalController::class, 'submit'])->name('aje.submit');
        Route::post('/aje/{id}/approve', [AdjJournalController::class, 'approve'])->name('aje.approve');
        Route::post('/aje/{id}/reject', [AdjJournalController::class, 'reject'])->name('aje.reject');
        Route::post('/aje/{id}/reverse', [AdjJournalController::class, 'reverse'])->name('aje.reverse');
        Route::get('/aje/{id}/pdf', [AdjJournalController::class, 'exportPdf'])->name('aje.pdf');

        // 4-Column Working Sheet (Trial Balance & Drilldown)
        Route::get('/trial-balance', [TrialBalanceController::class, 'index'])->name('trial-balance.index');
        Route::get('/trial-balance/drilldown/{fsliId}', [TrialBalanceController::class, 'drilldown'])->name('trial-balance.drilldown');
        Route::get('/trial-balance/export', [TrialBalanceController::class, 'exportExcel'])->name('trial-balance.export');

        // 4 Standard Financial Statements (SAK Compliant)
        Route::get('/financial-statements', [FinancialStatementController::class, 'index'])->name('financial-statements.index');
        Route::get('/financial-statements/pdf', [FinancialStatementController::class, 'exportPdf'])->name('financial-statements.pdf');

        // Budget vs Actual P&L Monitoring (3 Views)
        Route::get('/budget-pnl', [BudgetPnLController::class, 'index'])->name('budget-pnl.index');
        Route::get('/budget-pnl/pdf', [BudgetPnLController::class, 'exportPdf'])->name('budget-pnl.pdf');

        // Additional Anggaran Menus
        Route::get('/dynamic-budget', [ModuleFeatureController::class, 'render'])->name('dynamic-budget');
        Route::get('/monitoring', [ModuleFeatureController::class, 'render'])->name('monitoring');
        Route::get('/forecasts', [ModuleFeatureController::class, 'render'])->name('forecasts');
        Route::get('/budgeted-statements', [ModuleFeatureController::class, 'render'])->name('budgeted-statements');
        Route::get('/variance-analysis', [ModuleFeatureController::class, 'render'])->name('variance-analysis');
        Route::get('/laporan', [ModuleFeatureController::class, 'render'])->name('laporan');
        Route::get('/departments', [ModuleFeatureController::class, 'render'])->name('departments');
        Route::get('/production-plans', [ModuleFeatureController::class, 'render'])->name('production-plans');
        Route::get('/revenue-plans', [ModuleFeatureController::class, 'render'])->name('revenue-plans');
        Route::get('/staging', [ModuleFeatureController::class, 'render'])->name('staging');

        // Additional Akuntansi Menus
        Route::get('/paid-in-capital', [ModuleFeatureController::class, 'render'])->name('paid-in-capital');
        Route::get('/npd', [ModuleFeatureController::class, 'render'])->name('npd');

        // Balanced Scorecard (BSC) & 19 Financial Ratios
        Route::get('/financial-ratios', [FinancialRatioController::class, 'index'])->name('financial-ratios.index');
        Route::post('/financial-ratios/{periodId}/sync-hris', [FinancialRatioController::class, 'syncHris'])->name('financial-ratios.sync-hris');
        Route::post('/financial-ratios/{periodId}/publish-hris', [FinancialRatioController::class, 'publishToHris'])->name('financial-ratios.publish-hris');
        Route::post('/financial-ratios/config/{id}', [FinancialRatioController::class, 'updateConfiguration'])->name('financial-ratios.config.update');

        // 9-Point Automatic Reconciliation (R1 - R9)
        Route::get('/reconciliation', [ReconciliationController::class, 'index'])->name('reconciliation.index');
        Route::post('/reconciliation/run', [ReconciliationController::class, 'run'])->name('reconciliation.run');

        // Data & Audit Menus
        Route::get('/odoo-croscheck', [ModuleFeatureController::class, 'render'])->name('odoo-croscheck');
        Route::get('/coa-mapping', [ModuleFeatureController::class, 'render'])->name('coa-mapping');
        Route::get('/activity-logs', [ModuleFeatureController::class, 'render'])->name('activity-logs');

        // Integrasi Menus
        Route::get('/publications', [ModuleFeatureController::class, 'render'])->name('publications');
        Route::get('/kpi-pipeline', [ModuleFeatureController::class, 'render'])->name('kpi-pipeline');
        Route::get('/headcounts', [ModuleFeatureController::class, 'render'])->name('headcounts');
        Route::get('/bsc-integration', [ModuleFeatureController::class, 'render'])->name('bsc-integration');
        Route::get('/ratios-configurations', [ModuleFeatureController::class, 'render'])->name('ratios-configurations');
        Route::get('/production-feeds', [ModuleFeatureController::class, 'render'])->name('production-feeds');

        // Admin Menus
        Route::get('/fiscal-years', [ModuleFeatureController::class, 'render'])->name('fiscal-years');
        Route::get('/users', [ModuleFeatureController::class, 'render'])->name('users');
        Route::get('/settings', [ModuleFeatureController::class, 'render'])->name('settings');
    });
