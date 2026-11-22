<?php

use App\Core\Controllers\ModuleFeatureController;
use App\Modules\Rnd\Controllers\ApprovalCenterController;
use App\Modules\Rnd\Controllers\FormulaController;
use App\Modules\Rnd\Controllers\NpdProposalController;
use App\Modules\Rnd\Controllers\PrfController;
use App\Modules\Rnd\Controllers\RndController;
use App\Modules\Rnd\Controllers\SampleEvaluationController;
use App\Modules\Rnd\Controllers\TrialPmController;
use App\Modules\Rnd\Controllers\TrialRmController;
use Illuminate\Support\Facades\Route;

Route::prefix('rnd')
    ->name('rnd.')
    ->group(function () {
        // Module Overview Landing Dashboard & Timeline
        Route::get('/', [RndController::class, 'index'])->name('index');
        Route::get('/timeline/pipeline', [ModuleFeatureController::class, 'render'])->name('timeline.pipeline');
        Route::get('/timeline/product-cards', [ModuleFeatureController::class, 'render'])->name('timeline.product-cards');
        Route::get('/timeline/my-pending', [ModuleFeatureController::class, 'render'])->name('timeline.my-pending');
        Route::get('/timeline/audit-feed', [ModuleFeatureController::class, 'render'])->name('timeline.audit-feed');

        // Fase 1: Insight & Concept
        Route::get('/prfs', [PrfController::class, 'index'])->name('prfs.index');
        Route::get('/prfs/create', [PrfController::class, 'create'])->name('prfs.create');
        Route::post('/prfs', [PrfController::class, 'store'])->name('prfs.store');

        Route::get('/npd-proposals', [NpdProposalController::class, 'index'])->name('npd-proposals.index');
        Route::get('/npd-proposals/create', [NpdProposalController::class, 'create'])->name('npd-proposals.create');
        Route::post('/npd-proposals', [NpdProposalController::class, 'store'])->name('npd-proposals.store');

        // Fase 2: Development — Kajian & Resep
        Route::get('/preformulation-studies', [ModuleFeatureController::class, 'render'])->name('preformulation-studies');
        Route::get('/qbds', [ModuleFeatureController::class, 'render'])->name('qbds');

        // Formulasi Raw Material (RM)
        Route::get('/formulas', [FormulaController::class, 'index'])->name('formulas.index');
        Route::get('/formulas/create', [FormulaController::class, 'create'])->name('formulas.create');
        Route::post('/formulas', [FormulaController::class, 'store'])->name('formulas.store');
        Route::get('/formulas/{id}', [FormulaController::class, 'show'])->name('formulas.show');
        Route::post('/formulas/{id}/submit', [FormulaController::class, 'submit'])->name('formulas.submit');
        Route::post('/formulas/{id}/promote-to-bom', [FormulaController::class, 'promoteToBom'])->name('formulas.promote-to-bom');
        Route::post('/formulas/{id}/reformula', [FormulaController::class, 'reformula'])->name('formulas.reformula');
        Route::get('/formulas/{id}/export/pdf', [FormulaController::class, 'exportPdf'])->name('formulas.export.pdf');

        // Catatan Trial Raw Material (RM)
        Route::get('/trial-rms', [TrialRmController::class, 'index'])->name('trial-rms.index');
        Route::get('/trial-rms/create', [TrialRmController::class, 'create'])->name('trial-rms.create');
        Route::post('/trial-rms', [TrialRmController::class, 'store'])->name('trial-rms.store');
        Route::get('/trial-rms/reformula', [ModuleFeatureController::class, 'render'])->name('trial-rms.reformula');
        Route::get('/trial-rms/{id}', [TrialRmController::class, 'show'])->name('trial-rms.show');

        // Catatan Trial Packaging Material (PM) & Logbook
        Route::get('/trial-pms', [TrialPmController::class, 'index'])->name('trial-pms.index');
        Route::get('/trial-pms/create', [TrialPmController::class, 'create'])->name('trial-pms.create');
        Route::post('/trial-pms', [TrialPmController::class, 'store'])->name('trial-pms.store');
        Route::get('/logbook-pm', [ModuleFeatureController::class, 'render'])->name('logbook-pm');
        Route::get('/trial-pms/{id}', [TrialPmController::class, 'show'])->name('trial-pms.show');
        Route::post('/trial-pms/{id}/sign', [TrialPmController::class, 'signDepartment'])->name('trial-pms.sign');
        Route::post('/trial-pms/{id}/approve-final', [TrialPmController::class, 'approveFinal'])->name('trial-pms.approve-final');

        // Evaluation & Testing
        Route::get('/sample-evaluations', [SampleEvaluationController::class, 'index'])->name('sample-evaluations.index');
        Route::get('/sample-evaluations/create', [SampleEvaluationController::class, 'create'])->name('sample-evaluations.create');
        Route::post('/sample-evaluations', [SampleEvaluationController::class, 'store'])->name('sample-evaluations.store');
        Route::get('/stability-tests', [ModuleFeatureController::class, 'render'])->name('stability-tests');

        // Pengesahan & Desain
        Route::get('/formula-approvals', [ModuleFeatureController::class, 'render'])->name('formula-approvals');
        Route::get('/design-approvals', [ModuleFeatureController::class, 'render'])->name('design-approvals');

        // Regulasi, Alih Teknologi & Komersial
        Route::get('/regulatory-dossier', [ModuleFeatureController::class, 'render'])->name('regulatory-dossier');
        Route::get('/technology-transfers', [ModuleFeatureController::class, 'render'])->name('technology-transfers');
        Route::get('/nie-approvals', [ModuleFeatureController::class, 'render'])->name('nie-approvals');
        Route::get('/commercial-productions', [ModuleFeatureController::class, 'render'])->name('commercial-productions');

        // Single Approval Center (OM & GM)
        Route::get('/approval-center', [ApprovalCenterController::class, 'index'])->name('approval-center.index');
        Route::post('/approvals/{type}/{id}/approve', [ApprovalCenterController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{type}/{id}/reject', [ApprovalCenterController::class, 'reject'])->name('approvals.reject');

        // Master Data & Integrasi Pengadaan
        Route::get('/products', [ModuleFeatureController::class, 'render'])->name('products');
        Route::get('/materials', [ModuleFeatureController::class, 'render'])->name('materials');
        Route::get('/material-suppliers', [ModuleFeatureController::class, 'render'])->name('material-suppliers');
        Route::get('/procurement-prices', [ModuleFeatureController::class, 'render'])->name('procurement-prices');
        Route::get('/procurement-prices/mapping', [ModuleFeatureController::class, 'render'])->name('procurement-prices.mapping');
        Route::get('/admin/settings', [ModuleFeatureController::class, 'render'])->name('admin.settings');
    });
