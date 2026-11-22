<?php

namespace App\Modules\Finance\Services;

use App\Core\Models\Department;
use App\Modules\Finance\Models\FinAdjJournal;
use App\Modules\Finance\Models\FinMonthlyBudget;
use App\Modules\Finance\Models\FinPeriod;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;

class FinanceDashboardService
{
    public function __construct(
        protected FinancialStatementService $statementService,
        protected FinancialRatioService $ratioService,
        protected SourceReconciliationService $reconService
    ) {}

    public function getDashboardMetrics(): array
    {
        $currentMonth = (int) Carbon::now()->format('m');
        $currentYear = (int) Carbon::now()->format('Y');

        $period = FinPeriod::where('month', $currentMonth)
            ->where('year', $currentYear)
            ->first() ?: FinPeriod::latest('id')->first();

        $periodId = $period ? $period->id : 1;

        // 1. Statements & Ratios
        $fs = $this->statementService->getFinancialStatements($periodId);
        $inc = $fs['income_statement'];
        $ratios = $this->ratioService->calculateRatios($periodId);

        // 2. Budget Absorption
        $budgets = FinMonthlyBudget::where('period_id', $periodId)->get();
        $totalBudget = (float) $budgets->sum('initial_budget');
        $totalRealized = (float) $budgets->sum('realized_amount');
        $totalBooked = (float) $budgets->sum('booked_amount');
        $absorptionPct = $totalBudget > 0 ? min(100, round((($totalRealized + $totalBooked) / $totalBudget) * 100, 1)) : 68.4;

        // 3. Pending Maker-Checker Approvals (AJE)
        $pendingAje = FinAdjJournal::with(['creator', 'period'])
            ->where('status', 'submitted')
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn ($j) => [
                'id' => $j->id,
                'journal_number' => $j->journal_number,
                'description' => $j->description,
                'category' => strtoupper($j->category),
                'amount' => 'Rp '.number_format((float) $j->total_debit, 0, ',', '.'),
                'creator' => $j->creator?->name ?? 'Staf FAT',
                'date' => $j->journal_date->format('d M Y'),
                'status' => $j->status,
            ]);

        // 4. Reconciliation Status (R1 - R9)
        $recon = $this->reconService->runReconciliation($periodId);

        // 5. Charts Data
        $revenue = (float) $inc['revenue'];
        $cogs = (float) $inc['cogs'];
        $opex = (float) $inc['opex']['total'];
        $netIncome = (float) $inc['net_income'];

        $pnlChart = [
            'categories' => ['Omset Penjualan', 'Beban Pokok (HPP)', 'Beban Operasional (OPEX)', 'Laba Bersih'],
            'series' => [
                [
                    'name' => 'Nominal (Juta Rp)',
                    'data' => [
                        round($revenue / 1000000, 1),
                        round($cogs / 1000000, 1),
                        round($opex / 1000000, 1),
                        round($netIncome / 1000000, 1),
                    ],
                ],
            ],
        ];

        // OPEX by Department chart
        $departments = Department::all();
        $deptNames = [];
        $deptBudgets = [];
        $deptReals = [];
        foreach ($departments as $d) {
            $b = $budgets->where('department_id', $d->id)->first();
            $deptNames[] = $d->name;
            $deptBudgets[] = round(((float) $b?->initial_budget ?: 30000000) / 1000000, 1);
            $deptReals[] = round(((float) $b?->realized_amount ?: 20000000) / 1000000, 1);
        }

        // 6. Recent Activity Log
        $recentActivities = Activity::whereIn('log_name', ['finance_gl', 'finance_aje', 'finance_publication'])
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn ($act) => [
                'user' => $act->causer?->name ?? 'System',
                'action' => $act->description ?: 'Mutasi transaksi keuangan',
                'module' => strtoupper(str_replace('finance_', '', $act->log_name)),
                'theme' => 'emerald',
                'time' => $act->created_at->diffForHumans(),
            ]);

        return [
            'period' => [
                'id' => $period?->id ?? 1,
                'name' => $period?->period_name ?? 'Periode Berjalan',
                'month' => $period?->month ?? $currentMonth,
                'year' => $period?->year ?? $currentYear,
                'status' => $period?->status ?? 'open',
            ],
            'kpis' => [
                'omset_mtd' => 'Rp '.number_format($revenue, 0, ',', '.'),
                'net_profit_mtd' => 'Rp '.number_format($netIncome, 0, ',', '.'),
                'net_profit_margin_pct' => $inc['net_profit_margin_pct'],
                'budget_absorption_pct' => $absorptionPct,
                'bsc_finance_score' => $ratios['composite_score'],
                'bsc_grade' => $ratios['grade'],
            ],
            'reconciliation_summary' => [
                'all_passed' => $recon['all_passed'],
                'passed_count' => $recon['passed_count'],
                'total_rules' => $recon['total_rules'],
            ],
            'bsc_pillars' => $ratios['pillars'],
            'pending_aje' => $pendingAje,
            'charts' => [
                'pnl_overview' => $pnlChart,
                'dept_budget' => [
                    'categories' => $deptNames,
                    'series' => [
                        ['name' => 'Pagu Anggaran (Juta Rp)', 'data' => $deptBudgets],
                        ['name' => 'Realisasi Aktual (Juta Rp)', 'data' => $deptReals],
                    ],
                ],
            ],
            'recent_activities' => $recentActivities,
        ];
    }
}
