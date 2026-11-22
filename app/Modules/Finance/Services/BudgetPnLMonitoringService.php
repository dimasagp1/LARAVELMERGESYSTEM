<?php

namespace App\Modules\Finance\Services;

use App\Core\Models\Department;
use App\Modules\Finance\Models\FinGlEntry;
use App\Modules\Finance\Models\FinMonthlyBudget;
use App\Modules\Finance\Models\FinPeriod;
use App\Modules\Finance\Models\FinRevenuePlan;

class BudgetPnLMonitoringService
{
    public function __construct(
        protected FinancialStatementService $statementService
    ) {}

    /**
     * Compute Side-by-Side Budget vs Actual P&L with 3 views.
     */
    public function getBudgetVsActual(int $periodId): array
    {
        $period = FinPeriod::with('fiscalYear')->find($periodId);
        $fs = $this->statementService->getFinancialStatements($periodId);
        $inc = $fs['income_statement'];

        // Target Plans
        $fiscalYearId = $period?->fiscal_year_id ?? 1;
        $month = $period?->month ?? 11;

        $revPlan = FinRevenuePlan::where('fiscal_year_id', $fiscalYearId)
            ->where('month', $month)
            ->first();

        $budgetRevenue = $revPlan ? (float) $revPlan->target_revenue : 1500000000.0;
        $budgetCogs = $budgetRevenue * 0.45; // 45% standard gross margin target
        $budgetGrossProfit = $budgetRevenue - $budgetCogs;

        $deptBudgets = FinMonthlyBudget::where('period_id', $periodId)->get();
        $budgetOpex = (float) $deptBudgets->sum('initial_budget');
        if ($budgetOpex <= 0) {
            $budgetOpex = 350000000.0;
        }

        $budgetNetIncome = $budgetGrossProfit - $budgetOpex;

        // Actual values
        $actRevenue = (float) $inc['revenue'];
        $actCogs = (float) $inc['cogs'];
        $actGrossProfit = (float) $inc['gross_profit'];
        $actOpex = (float) $inc['opex']['total'];
        $actNetIncome = (float) $inc['net_income'];

        // Helper for variance calculation
        $calcVar = function (float $budget, float $actual, bool $higherIsBetter = true) {
            $diff = $actual - $budget;
            $pct = $budget > 0 ? round(($diff / $budget) * 100, 2) : 0;
            $favorable = $higherIsBetter ? ($diff >= 0) : ($diff <= 0);

            return [
                'budget' => $budget,
                'actual' => $actual,
                'variance_rp' => $diff,
                'variance_pct' => $pct,
                'is_favorable' => $favorable,
                'badge' => $favorable ? 'favorable' : 'unfavorable',
            ];
        };

        // View 1: P&L Summary
        $pnlSummary = [
            'revenue' => $calcVar($budgetRevenue, $actRevenue, true),
            'cogs' => $calcVar($budgetCogs, $actCogs, false),
            'gross_profit' => $calcVar($budgetGrossProfit, $actGrossProfit, true),
            'opex' => $calcVar($budgetOpex, $actOpex, false),
            'net_income' => $calcVar($budgetNetIncome, $actNetIncome, true),
        ];

        // View 2: OPEX per 11 Departments
        $departments = Department::all();
        $deptOpexBreakdown = [];

        foreach ($departments as $dept) {
            $budgetRow = $deptBudgets->where('department_id', $dept->id)->first();
            $dBudget = $budgetRow ? (float) $budgetRow->initial_budget : 30000000.0;

            // Actual opex from GL entries mapped to this dept
            $dActual = (float) FinGlEntry::where('period_id', $periodId)
                ->where('department_id', $dept->id)
                ->whereHas('fsli', fn ($q) => $q->where('statement_type', 'income_statement')->where('code', 'like', 'OPEX%'))
                ->sum('debit');

            if ($dActual <= 0 && $budgetRow) {
                $dActual = (float) $budgetRow->realized_amount;
            }

            $diff = $dActual - $dBudget;
            $pct = $dBudget > 0 ? round(($dActual / $dBudget) * 100, 1) : 0;

            $deptOpexBreakdown[] = [
                'department_id' => $dept->id,
                'department_name' => $dept->name,
                'department_code' => $dept->code,
                'budget' => $dBudget,
                'actual' => $dActual,
                'burn_rate_pct' => $pct,
                'is_overbudget' => $dActual > $dBudget,
                'variance_rp' => $diff,
            ];
        }

        // View 3: Run-Rate Trend (6 months / periods)
        $periods = FinPeriod::where('fiscal_year_id', $fiscalYearId)
            ->where('month', '<=', $month)
            ->orderBy('month')
            ->get();

        $runRateCategories = [];
        $runRateRevenue = [];
        $runRateNetIncome = [];

        foreach ($periods as $p) {
            $runRateCategories[] = $p->period_name;
            $rev = FinGlEntry::where('period_id', $p->id)
                ->whereHas('fsli', fn ($q) => $q->where('code', 'REV_SALES'))
                ->sum('credit');
            $runRateRevenue[] = round(((float) $rev ?: $budgetRevenue) / 1000000, 1); // in Million Rp
            $runRateNetIncome[] = round(($actNetIncome ?: $budgetNetIncome) / 1000000, 1);
        }

        return [
            'period' => $fs['period'],
            'pnl_summary' => $pnlSummary,
            'department_opex' => $deptOpexBreakdown,
            'run_rate_trend' => [
                'categories' => $runRateCategories,
                'series' => [
                    ['name' => 'Pendapatan (Juta Rp)', 'data' => $runRateRevenue],
                    ['name' => 'Laba Bersih (Juta Rp)', 'data' => $runRateNetIncome],
                ],
            ],
        ];
    }
}
