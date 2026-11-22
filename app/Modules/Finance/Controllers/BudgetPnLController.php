<?php

namespace App\Modules\Finance\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\FinPeriod;
use App\Modules\Finance\Services\BudgetPnLMonitoringService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class BudgetPnLController extends Controller
{
    public function __construct(
        protected BudgetPnLMonitoringService $budgetService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $periods = FinPeriod::orderByDesc('id')->get();
        $selectedPeriodId = (int) $request->input('period_id', $periods->first()?->id ?? 1);

        $data = $this->budgetService->getBudgetVsActual($selectedPeriodId);

        return Inertia::render('Modules/Finance/BudgetPnL/Index', [
            'module' => [
                'name' => 'Budget vs Actual P&L',
                'slug' => 'finance',
                'color_theme' => 'emerald',
                'menus' => $this->moduleManager->getModuleMenus('finance'),
            ],
            'budgetData' => $data,
            'periods' => $periods,
            'selectedPeriodId' => $selectedPeriodId,
        ]);
    }

    public function exportPdf(Request $request): HttpResponse
    {
        $periodId = (int) $request->input('period_id', 1);
        $data = $this->budgetService->getBudgetVsActual($periodId);

        $pdf = Pdf::loadView('pdf.finance_budget_pnl', [
            'period' => $data['period'],
            'budgetVsActual' => $data,
            'pnl_summary' => $data['pnl_summary'],
            'department_opex' => $data['department_opex'],
        ])->setPaper('a4', 'landscape');

        return $pdf->download("Budget_vs_Actual_PnL_{$data['period']['name']}.pdf");
    }
}
