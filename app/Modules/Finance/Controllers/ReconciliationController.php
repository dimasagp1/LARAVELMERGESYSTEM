<?php

namespace App\Modules\Finance\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\FinPeriod;
use App\Modules\Finance\Models\FinSourceReconciliation;
use App\Modules\Finance\Services\SourceReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReconciliationController extends Controller
{
    public function __construct(
        protected SourceReconciliationService $reconciliationService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $periods = FinPeriod::orderByDesc('id')->get();
        $selectedPeriodId = (int) $request->input('period_id', $periods->first()?->id ?? 1);

        $reconciliations = FinSourceReconciliation::where('period_id', $selectedPeriodId)->get();

        // If no records yet, run once automatically
        if ($reconciliations->isEmpty()) {
            $data = $this->reconciliationService->runReconciliation($selectedPeriodId);
            $reconciliations = FinSourceReconciliation::where('period_id', $selectedPeriodId)->get();
        } else {
            $data = [
                'period' => FinPeriod::find($selectedPeriodId)?->toArray(),
                'all_passed' => $reconciliations->every(fn ($r) => (bool) $r->is_passed),
                'passed_count' => $reconciliations->where('is_passed', true)->count(),
                'total_rules' => $reconciliations->count(),
                'rules' => $reconciliations->map(fn ($r) => [
                    'code' => $r->rule_code,
                    'name' => $r->rule_name,
                    'gl_value' => (float) $r->gl_value,
                    'target_value' => (float) $r->target_value,
                    'variance' => (float) $r->variance,
                    'is_passed' => (bool) $r->is_passed,
                    'checked_at' => $r->checked_at?->format('d M Y H:i'),
                ]),
            ];
        }

        return Inertia::render('Modules/Finance/Reconciliation/Index', [
            'module' => [
                'name' => 'Rekonsiliasi Otomatis (R1-R9)',
                'slug' => 'finance',
                'color_theme' => 'emerald',
                'menus' => $this->moduleManager->getModuleMenus('finance'),
            ],
            'auditData' => $data,
            'periods' => $periods,
            'selectedPeriodId' => $selectedPeriodId,
        ]);
    }

    public function run(Request $request): RedirectResponse
    {
        $periodId = (int) $request->input('period_id', 1);
        $res = $this->reconciliationService->runReconciliation($periodId);

        $msg = $res['all_passed']
            ? "Semua {$res['total_rules']} titik rekonsiliasi lolos uji keselarasan (100% Passed)!"
            : "Audit selesai: {$res['passed_count']} dari {$res['total_rules']} lolos. Periksa varians.";

        return back()->with($res['all_passed'] ? 'success' : 'warning', $msg);
    }
}
