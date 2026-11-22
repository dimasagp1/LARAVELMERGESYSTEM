<?php

use App\Core\Controllers\ModuleFeatureController;
use App\Modules\Quality\Controllers\CapaController;
use App\Modules\Quality\Controllers\ChangeRequestController;
use App\Modules\Quality\Controllers\DeviationController;
use App\Modules\Quality\Controllers\MasterDocumentController;
use App\Modules\Quality\Controllers\QualityController;
use Illuminate\Support\Facades\Route;

Route::prefix('quality')
    ->name('quality.')
    ->group(function () {
        // Landing & Overview
        Route::get('/', [QualityController::class, 'index'])->name('index');

        // Master Induk Dokumen Mutu (Piramida 4-Tingkat)
        Route::prefix('documents')->name('documents.')->group(function () {
            Route::get('/', [MasterDocumentController::class, 'index'])->name('index');
            Route::get('/create', [MasterDocumentController::class, 'create'])->name('create');
            Route::post('/', [MasterDocumentController::class, 'store'])->name('store');
            Route::get('/folders', [ModuleFeatureController::class, 'render'])->name('folders');
            Route::get('/revisions', [ModuleFeatureController::class, 'render'])->name('revisions');
            Route::get('/links', [ModuleFeatureController::class, 'render'])->name('links');
            Route::get('/master-options', [ModuleFeatureController::class, 'render'])->name('master-options');
            Route::get('/sync', [ModuleFeatureController::class, 'render'])->name('sync');
            Route::get('/{document}/edit', [MasterDocumentController::class, 'edit'])->name('edit');
            Route::put('/{document}', [MasterDocumentController::class, 'update'])->name('update');
            Route::delete('/{document}', [MasterDocumentController::class, 'destroy'])->name('destroy');
            Route::post('/{document}/move', [MasterDocumentController::class, 'move'])->name('move');
            Route::post('/folders', [MasterDocumentController::class, 'storeFolder'])->name('folders.store');
        });

        // Change Requests (CRA & CRB)
        Route::prefix('change-requests')->name('change-requests.')->group(function () {
            Route::get('/', [ChangeRequestController::class, 'index'])->name('index');
            Route::get('/cra', [ModuleFeatureController::class, 'render'])->name('cra');
            Route::get('/crb', [ModuleFeatureController::class, 'render'])->name('crb');
            Route::get('/pic-assessments', [ModuleFeatureController::class, 'render'])->name('pic-assessments');
            Route::get('/stages', [ModuleFeatureController::class, 'render'])->name('stages');
            Route::get('/create', [ChangeRequestController::class, 'create'])->name('create');
            Route::post('/', [ChangeRequestController::class, 'store'])->name('store');
            Route::get('/{changeRequest}', [ChangeRequestController::class, 'show'])->name('show');
            Route::post('/{changeRequest}/assign-assessments', [ChangeRequestController::class, 'assignAssessments'])->name('assign-assessments');
            Route::post('/{changeRequest}/submit-assessment', [ChangeRequestController::class, 'submitAssessment'])->name('submit-assessment');
            Route::post('/{changeRequest}/approve', [ChangeRequestController::class, 'approve'])->name('approve');
            Route::post('/{changeRequest}/stage-2', [ChangeRequestController::class, 'submitStage2'])->name('stage-2');
            Route::post('/{changeRequest}/complete-stage-2', [ChangeRequestController::class, 'completeStage2'])->name('complete-stage-2');
            Route::get('/{changeRequest}/pdf', [ChangeRequestController::class, 'printPdf'])->name('pdf');
        });

        // Deviation Reports (DR) & Investigations (PK)
        Route::prefix('deviations')->name('deviations.')->group(function () {
            Route::get('/', [DeviationController::class, 'index'])->name('index');
            Route::get('/create', [DeviationController::class, 'create'])->name('create');
            Route::post('/', [DeviationController::class, 'store'])->name('store');
            Route::get('/numbering', [ModuleFeatureController::class, 'render'])->name('numbering');
            Route::get('/superiors', [ModuleFeatureController::class, 'render'])->name('superiors');
            Route::get('/{deviation}/investigation', [DeviationController::class, 'investigation'])->name('investigation');
            Route::post('/{deviation}/investigation', [DeviationController::class, 'updateInvestigation'])->name('investigation.update');
            Route::post('/{deviation}/assign-mengetahui', [DeviationController::class, 'assignMengetahui'])->name('assign-mengetahui');
            Route::post('/{deviation}/submit-mengetahui', [DeviationController::class, 'submitMengetahui'])->name('submit-mengetahui');
            Route::post('/{deviation}/decide', [DeviationController::class, 'decide'])->name('decide');
            Route::get('/{deviation}/pdf', [DeviationController::class, 'printPdf'])->name('pdf');
        });

        // Penyelidikan Ketidaksesuaian (Investigations)
        Route::get('/deviations-investigations', [ModuleFeatureController::class, 'render'])->name('deviations-investigations');
        Route::get('/deviations-investigations/fishbone', [ModuleFeatureController::class, 'render'])->name('deviations-investigations.fishbone');
        Route::get('/deviations-investigations/five-whys', [ModuleFeatureController::class, 'render'])->name('deviations-investigations.five-whys');
        Route::get('/deviations-investigations/fmea', [ModuleFeatureController::class, 'render'])->name('deviations-investigations.fmea');
        Route::get('/deviations-investigations/decisions', [ModuleFeatureController::class, 'render'])->name('deviations-investigations.decisions');

        // CAPA (Corrective and Preventive Action)
        Route::prefix('capa')->name('capa.')->group(function () {
            Route::get('/', [CapaController::class, 'index'])->name('index');
            Route::get('/actions', [ModuleFeatureController::class, 'render'])->name('actions');
            Route::get('/evidences', [ModuleFeatureController::class, 'render'])->name('evidences');
            Route::get('/verifications', [ModuleFeatureController::class, 'render'])->name('verifications');
            Route::get('/{capa}', [CapaController::class, 'show'])->name('show');
            Route::post('/{capa}/actions', [CapaController::class, 'storeAction'])->name('actions.store');
            Route::post('/actions/{action}/proof', [CapaController::class, 'submitProof'])->name('actions.proof');
            Route::post('/{capa}/verify', [CapaController::class, 'verify'])->name('verify');
            Route::get('/{capa}/pdf', [CapaController::class, 'printPdf'])->name('pdf');
        });

        // Tanda Tangan & Cetak A4
        Route::get('/signatures', [ModuleFeatureController::class, 'render'])->name('signatures');
        Route::get('/print/change-requests', [ModuleFeatureController::class, 'render'])->name('print.change-requests');
        Route::get('/print/deviations', [ModuleFeatureController::class, 'render'])->name('print.deviations');
        Route::get('/print/investigations', [ModuleFeatureController::class, 'render'])->name('print.investigations');

        // Admin Mutu & Konfigurasi
        Route::get('/admin/users', [ModuleFeatureController::class, 'render'])->name('admin.users');
        Route::get('/admin/branding', [ModuleFeatureController::class, 'render'])->name('admin.branding');
        Route::get('/admin/settings', [ModuleFeatureController::class, 'render'])->name('admin.settings');
    });
