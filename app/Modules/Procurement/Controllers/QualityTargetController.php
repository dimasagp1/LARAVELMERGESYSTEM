<?php

namespace App\Modules\Procurement\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Procurement\Models\ProductionLossTimeLog;
use App\Modules\Procurement\Services\KpiQualityService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class QualityTargetController extends Controller
{
    public function __construct(
        protected KpiQualityService $kpiService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $year = (int) $request->input('fiscal_year', Carbon::now()->format('Y'));
        $month = (int) $request->input('month', Carbon::now()->format('m'));

        $summary = $this->kpiService->calculateQualityTargets($year, $month);
        $lossLogs = ProductionLossTimeLog::with('creator')
            ->whereYear('log_date', $year)
            ->latest('log_date')
            ->get();

        return Inertia::render('Modules/Procurement/QualityTarget/Index', [
            'module' => [
                'name' => 'Sasaran Mutu 2026 & KPI',
                'slug' => 'procurement',
                'color_theme' => 'sky',
                'menus' => $this->moduleManager->getModuleMenus('procurement'),
            ],
            'summary' => $summary,
            'lossLogs' => $lossLogs,
            'fiscalYear' => $year,
        ]);
    }

    public function storeLossTime(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'log_date' => 'required|date',
            'machine_name' => 'required|string|max:100',
            'downtime_hours' => 'required|numeric|min:0.1',
            'material_name' => 'required|string|max:255',
            'output_per_hour' => 'required|numeric|min:1',
            'product_unit_value' => 'required|numeric|min:1',
            'target_revenue' => 'nullable|numeric|min:1',
        ]);

        $log = new ProductionLossTimeLog($validated);
        $log->created_by = $request->user()?->id ?? User::first()?->id;
        $log->calculateLossValue();
        $log->save();

        return back()->with('success', 'Kejadian Loss Time produksi akibat keterlambatan material berhasil dicatat.');
    }

    public function exportPdf(Request $request): HttpResponse
    {
        $year = (int) $request->input('fiscal_year', Carbon::now()->format('Y'));
        $summary = $this->kpiService->calculateQualityTargets($year);

        $pdf = Pdf::loadView('pdf.procurement_quality', [
            'summary' => $summary,
            'fiscalYear' => $year,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("Sasaran_Mutu_Procurement_{$year}.pdf");
    }
}
