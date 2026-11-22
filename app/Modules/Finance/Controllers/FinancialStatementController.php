<?php

namespace App\Modules\Finance\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\FinPeriod;
use App\Modules\Finance\Services\FinancialStatementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class FinancialStatementController extends Controller
{
    public function __construct(
        protected FinancialStatementService $statementService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $periods = FinPeriod::orderByDesc('id')->get();
        $selectedPeriodId = (int) $request->input('period_id', $periods->first()?->id ?? 1);

        $statements = $this->statementService->getFinancialStatements($selectedPeriodId);

        return Inertia::render('Modules/Finance/FinancialStatement/Index', [
            'module' => [
                'name' => 'Laporan Keuangan Standar',
                'slug' => 'finance',
                'color_theme' => 'emerald',
                'menus' => $this->moduleManager->getModuleMenus('finance'),
            ],
            'statements' => $statements,
            'periods' => $periods,
            'selectedPeriodId' => $selectedPeriodId,
        ]);
    }

    public function exportPdf(Request $request): HttpResponse
    {
        $periodId = (int) $request->input('period_id', 1);
        $statements = $this->statementService->getFinancialStatements($periodId);

        $pdf = Pdf::loadView('pdf.finance_statement', [
            'period' => $statements['period'],
            'statements' => $statements,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("Laporan_Keuangan_{$statements['period']['name']}.pdf");
    }
}
