<?php

namespace App\Modules\Finance\Controllers;

use App\Core\Integrations\Contracts\HrisIntegrationContract;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\FinPeriod;
use App\Modules\Finance\Models\FinRatioConfiguration;
use App\Modules\Finance\Services\FinancialRatioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FinancialRatioController extends Controller
{
    public function __construct(
        protected FinancialRatioService $ratioService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $periods = FinPeriod::orderByDesc('id')->get();
        $selectedPeriodId = (int) $request->input('period_id', $periods->first()?->id ?? 1);

        $bscData = $this->ratioService->calculateRatios($selectedPeriodId);
        $configurations = FinRatioConfiguration::orderBy('pillar')->orderBy('sort_order')->get();

        return Inertia::render('Modules/Finance/FinancialRatio/Index', [
            'module' => [
                'name' => 'Balanced Scorecard (BSC) & Rasio Keuangan',
                'slug' => 'finance',
                'color_theme' => 'emerald',
                'menus' => $this->moduleManager->getModuleMenus('finance'),
            ],
            'bscData' => $bscData,
            'configurations' => $configurations,
            'periods' => $periods,
            'selectedPeriodId' => $selectedPeriodId,
        ]);
    }

    public function updateConfiguration(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'target_value' => 'required|numeric',
            'weight' => 'nullable|numeric|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        $config = FinRatioConfiguration::findOrFail($id);
        $config->update($validated);

        return back()->with('success', "Target rasio {$config->ratio_code} berhasil diperbarui.");
    }

    /**
     * Synchronize Headcount & Working Hours from HRIS to 19 BSC Productivity Ratios.
     */
    public function syncHris(Request $request, int $periodId, HrisIntegrationContract $hrisIntegration): RedirectResponse
    {
        $headcount = $hrisIntegration->syncHeadcountToFinance($periodId);

        return back()->with('success', "Sinkronisasi HRIS Berhasil: {$headcount->total_headcount} karyawan ({$headcount->direct_labor_count} TKL) dan {$headcount->working_hours} jam kerja berhasil diperbarui ke 19 Rasio Produktivitas BSC.");
    }

    /**
     * Publish financial ratios & variables to HRIS Level 3 Quality Objectives.
     */
    public function publishToHris(Request $request, int $periodId, HrisIntegrationContract $hrisIntegration): RedirectResponse
    {
        $bscData = $this->ratioService->calculateRatios($periodId);
        $count = $hrisIntegration->publishFinanceMetricsToHris($periodId, $bscData['raw_ratios'] ?? []);

        return back()->with('success', "Variabel keuangan terpublikasi ke {$count} Sasaran Mutu Level 3 di modul HRIS.");
    }
}
