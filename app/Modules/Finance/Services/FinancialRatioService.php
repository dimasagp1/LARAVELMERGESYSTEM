<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FinHeadcount;
use App\Modules\Finance\Models\FinPaidInCapital;
use App\Modules\Finance\Models\FinPeriod;
use App\Modules\Finance\Models\FinRatioConfiguration;

class FinancialRatioService
{
    public function __construct(
        protected FinancialStatementService $statementService
    ) {}

    /**
     * Calculate 19 Financial Ratios across 5 BSC Pillars.
     */
    public function calculateRatios(int $periodId): array
    {
        $fs = $this->statementService->getFinancialStatements($periodId);
        $period = FinPeriod::find($periodId);

        $inc = $fs['income_statement'];
        $bs = $fs['balance_sheet'];

        $revenue = (float) $inc['revenue'];
        $cogs = (float) $inc['cogs'];
        $grossProfit = (float) $inc['gross_profit'];
        $netIncome = (float) $inc['net_income'];
        $salaryExpense = (float) $inc['opex']['salaries_wages'];

        $totalAssets = (float) $bs['assets']['total_assets'];
        $currentAssets = (float) $bs['assets']['current']['total'];
        $cash = (float) $bs['assets']['current']['cash'];
        $ar = (float) $bs['assets']['current']['accounts_receivable'];
        $inventory = (float) $bs['assets']['current']['inventory'];

        $totalLiabilities = (float) $bs['liabilities']['total_liabilities'];
        $currentLiabilities = (float) $bs['liabilities']['current']['total'];
        $ap = (float) $bs['liabilities']['current']['accounts_payable'];
        $totalEquity = (float) $bs['equity']['total_equity'];

        // Non-Odoo Denominators
        $headcount = FinHeadcount::where('period_id', $periodId)->first();
        $totalHeadcount = $headcount ? (int) $headcount->total_headcount : 45;
        $workingHours = $headcount ? (float) $headcount->working_hours : 7200.0;

        $paidInCap = FinPaidInCapital::where('period_id', $periodId)->first();
        $paidInCapital = $paidInCap ? (float) $paidInCap->amount : (float) $bs['equity']['paid_in_capital'];
        if ($paidInCapital <= 0) {
            $paidInCapital = 5000000000.0;
        } // 5 Billion fallback

        // Raw Metrics
        $rawRatios = [
            // 1. Profitability (30%)
            'GPM' => $revenue > 0 ? ($grossProfit / $revenue) * 100 : 0,
            'NPM' => $revenue > 0 ? ($netIncome / $revenue) * 100 : 0,
            'ROA' => $totalAssets > 0 ? (($netIncome * 12) / $totalAssets) * 100 : 0,
            'ROE' => $totalEquity > 0 ? (($netIncome * 12) / $totalEquity) * 100 : 0,

            // 2. Activity (25%)
            'ITO' => $inventory > 0 ? ($cogs / $inventory) : 0,
            'ATO' => $totalAssets > 0 ? ($revenue / $totalAssets) : 0,
            'ARTO' => $ar > 0 ? ($revenue / $ar) : 0,
            'DIO' => ($cogs > 0 && $inventory > 0) ? round((365 * $inventory) / $cogs, 1) : 45.0,
            'DSO' => ($revenue > 0 && $ar > 0) ? round((365 * $ar) / $revenue, 1) : 38.0,
            'DPO' => ($cogs > 0 && $ap > 0) ? round((365 * $ap) / $cogs, 1) : 42.0,

            // 3. Productivity (20%)
            'PROD_EMP' => $totalHeadcount > 0 ? ($revenue / $totalHeadcount) : 0,
            'PROD_HR' => $workingHours > 0 ? ($revenue / $workingHours) : 0,
            'PROD_LABOR_COST' => $salaryExpense > 0 ? ($revenue / $salaryExpense) : 0,
            'CPR' => $paidInCapital > 0 ? (($grossProfit + $salaryExpense) / $paidInCapital) * 100 : 0,

            // 4. Liquidity (15%)
            'CR' => $currentLiabilities > 0 ? ($currentAssets / $currentLiabilities) * 100 : 150.0,
            'CASH_R' => $currentLiabilities > 0 ? ($cash / $currentLiabilities) * 100 : 60.0,
            'QR' => $currentLiabilities > 0 ? (($currentAssets - $inventory) / $currentLiabilities) * 100 : 110.0,

            // 5. Solvency (10%)
            'DER' => $totalEquity > 0 ? ($totalLiabilities / $totalEquity) : 0.65,
            'DAR' => $totalAssets > 0 ? ($totalLiabilities / $totalAssets) : 0.35,
        ];

        // Configurations lookup or default definitions
        $configs = FinRatioConfiguration::where('is_active', true)->get()->keyBy('ratio_code');

        $pillars = [
            'profitability' => ['weight' => 30, 'score' => 0, 'ratios' => []],
            'activity' => ['weight' => 25, 'score' => 0, 'ratios' => []],
            'productivity' => ['weight' => 20, 'score' => 0, 'ratios' => []],
            'liquidity' => ['weight' => 15, 'score' => 0, 'ratios' => []],
            'solvency' => ['weight' => 10, 'score' => 0, 'ratios' => []],
        ];

        foreach ($rawRatios as $code => $val) {
            $conf = $configs->get($code);
            $target = $conf ? (float) $conf->target_value : 100.0;
            $polarity = $conf ? $conf->polarity : 'higher_is_better';
            $pillar = $conf ? $conf->pillar : 'profitability';

            // Score out of 100
            $ratioScore = 85.0; // baseline healthy
            if ($target > 0) {
                if ($polarity === 'higher_is_better') {
                    $ratioScore = min(100, max(0, round(($val / $target) * 100, 1)));
                } else {
                    $ratioScore = $val <= $target ? 100.0 : max(0, round((1 - (($val - $target) / $target)) * 100, 1));
                }
            }

            $status = $ratioScore >= 85 ? 'achieved' : ($ratioScore >= 70 ? 'warning' : 'critical');

            $itemData = [
                'code' => $code,
                'name' => $conf?->name ?? $code,
                'pillar' => $pillar,
                'raw_value' => round($val, 2),
                'target' => $target,
                'unit' => $conf?->unit ?? '%',
                'score' => $ratioScore,
                'status' => $status,
            ];

            if (isset($pillars[$pillar])) {
                $pillars[$pillar]['ratios'][] = $itemData;
            }
        }

        // Compute average score per pillar and composite score
        $compositeScore = 0;
        foreach ($pillars as $k => &$p) {
            $count = count($p['ratios']);
            $avgScore = $count > 0 ? array_sum(array_column($p['ratios'], 'score')) / $count : 80;
            $p['score'] = round($avgScore, 1);
            $compositeScore += ($p['score'] * ($p['weight'] / 100));
        }

        $compositeScore = round($compositeScore, 1);
        $grade = $compositeScore >= 90 ? 'A (Unggul)' : ($compositeScore >= 80 ? 'B (Baik)' : 'C (Cukup)');

        return [
            'period_id' => $periodId,
            'composite_score' => $compositeScore,
            'grade' => $grade,
            'pillars' => $pillars,
        ];
    }
}
