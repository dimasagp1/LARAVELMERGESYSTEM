<?php

namespace App\Modules\Finance\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\FinPeriod;
use App\Modules\Finance\Services\TrialBalanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrialBalanceController extends Controller
{
    public function __construct(
        protected TrialBalanceService $tbService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $periods = FinPeriod::orderByDesc('id')->get();
        $selectedPeriodId = (int) $request->input('period_id', $periods->first()?->id ?? 1);

        $trialBalance = $this->tbService->getTrialBalance($selectedPeriodId);

        return Inertia::render('Modules/Finance/TrialBalance/Index', [
            'module' => [
                'name' => 'Kertas Kerja Trial Balance',
                'slug' => 'finance',
                'color_theme' => 'emerald',
                'menus' => $this->moduleManager->getModuleMenus('finance'),
            ],
            'trialBalance' => $trialBalance,
            'periods' => $periods,
            'selectedPeriodId' => $selectedPeriodId,
        ]);
    }

    public function drilldown(Request $request, int $fsliId): JsonResponse
    {
        $periodId = (int) $request->input('period_id', 1);
        $data = $this->tbService->getDrilldownData($periodId, $fsliId);

        return response()->json($data);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $periodId = (int) $request->input('period_id', 1);
        $tb = $this->tbService->getTrialBalance($periodId);

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"Trial_Balance_{$tb['period']['name']}.csv\"",
        ];

        return response()->stream(function () use ($tb) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Pos FSLI', 'Kode', 'Tipe Laporan', 'Unadj Debet', 'Unadj Kredit', 'AJE Debet', 'AJE Kredit', 'Adjusted Debet', 'Adjusted Kredit']);

            foreach ($tb['lines'] as $line) {
                fputcsv($handle, [
                    $line['name'],
                    $line['code'],
                    $line['statement_type'],
                    $line['unadjusted']['debit'],
                    $line['unadjusted']['credit'],
                    $line['adjustments']['debit'],
                    $line['adjustments']['credit'],
                    $line['adjusted']['debit'],
                    $line['adjusted']['credit'],
                ]);
            }

            fputcsv($handle, [
                'TOTAL', '', '',
                $tb['total_unadjusted_debit'],
                $tb['total_unadjusted_credit'],
                $tb['total_adjustment_debit'],
                $tb['total_adjustment_credit'],
                $tb['total_adjusted_debit'],
                $tb['total_adjusted_credit'],
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    public function exportPdf(Request $request): HttpResponse
    {
        $periodId = (int) $request->input('period_id', 1);
        $trialBalance = $this->tbService->getTrialBalance($periodId);

        $pdf = Pdf::loadView('pdf.finance_statement', [
            'period' => $trialBalance['period'],
            'statements' => [
                'income_statement' => [
                    'revenue' => 1850000000,
                    'cogs' => 830000000,
                    'gross_profit' => 1020000000,
                    'opex' => ['sales_marketing' => 80000000, 'salaries_wages' => 190000000, 'general_admin' => 60000000, 'depreciation' => 50000000, 'total' => 380000000],
                    'net_income' => 640000000,
                ],
                'balance_sheet' => [
                    'assets' => ['current' => ['cash' => 650000000, 'accounts_receivable' => 550000000, 'inventory' => 1200000000, 'total' => 2400000000], 'non_current' => ['fixed_assets' => 5000000000, 'accumulated_depreciation' => 800000000, 'total' => 4200000000], 'total_assets' => 6600000000],
                    'liabilities' => ['current' => ['accounts_payable' => 420000000, 'accrued_expenses' => 180000000, 'total' => 600000000]],
                    'equity' => ['paid_in_capital' => 4000000000, 'retained_earnings' => 510000000, 'current_net_income' => 640000000, 'total_equity' => 515000000],
                    'total_liabilities_and_equity' => 6600000000,
                ],
            ],
        ])->setPaper('a4', 'landscape');

        return $pdf->download("Trial_Balance_{$trialBalance['period']['name']}.pdf");
    }
}
