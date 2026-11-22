<?php

namespace App\Modules\Finance\Services;

class FinancialStatementService
{
    public function __construct(
        protected TrialBalanceService $trialBalanceService
    ) {}

    /**
     * Generate the 4 Standard Financial Statements.
     */
    public function getFinancialStatements(int $periodId): array
    {
        $tb = $this->trialBalanceService->getTrialBalance($periodId);
        $rows = collect($tb['rows'])->keyBy('code');

        // Helper to extract net amount by FSLI code or aliases
        $getVal = function ($codes, string $type = 'debit') use ($rows): float {
            if (! is_array($codes)) {
                $codes = [$codes];
            }
            $sum = 0.0;
            foreach ($codes as $code) {
                $row = $rows->get($code);
                if ($row) {
                    $sum += ($type === 'credit')
                        ? (float) ($row['adjusted_credit'] - $row['adjusted_debit'])
                        : (float) ($row['adjusted_debit'] - $row['adjusted_credit']);
                }
            }

            return max(0, $sum);
        };

        // 1. Income Statement (Laporan Laba Rugi)
        $revenue = max(0, $getVal(['REV_SALES', 'REV'], 'credit'));
        $cogs = max(0, $getVal(['COGS_TOTAL', 'COGS'], 'debit'));
        $grossProfit = $revenue - $cogs;

        $opexSales = max(0, $getVal(['OPEX_MARKETING', 'OPEX_SALES'], 'debit'));
        $opexSalary = max(0, $getVal(['OPEX_SALARIES', 'OPEX_SALARY'], 'debit'));
        $opexGa = max(0, $getVal(['OPEX_GA'], 'debit'));
        $opexDeprec = max(0, $getVal(['OPEX_UTILITIES', 'OPEX_DEPREC'], 'debit'));
        $totalOpex = $opexSales + $opexSalary + $opexGa + $opexDeprec;

        $operatingIncome = $grossProfit - $totalOpex;
        $otherIncomeExpense = $getVal(['OTHER_FIN_EXP', 'OTHER_INC_EXP'], 'credit');
        $taxExpense = max(0, $getVal(['TAX_EXPENSE', 'TAX'], 'debit'));
        $netIncome = $operatingIncome + $otherIncomeExpense - $taxExpense;

        $incomeStatement = [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'gross_margin_pct' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0,
            'gross_profit_margin_pct' => $revenue > 0 ? round(($grossProfit / $revenue) * 100, 2) : 0,
            'opex' => [
                'sales_marketing' => $opexSales,
                'marketing_selling' => $opexSales,
                'salaries_wages' => $opexSalary,
                'general_admin' => $opexGa,
                'general_administrative' => $opexGa,
                'utilities_maintenance' => $opexDeprec,
                'depreciation' => $opexDeprec,
                'total' => $totalOpex,
            ],
            'operating_income' => $operatingIncome,
            'other_income_expense' => [
                'interest_expense' => $otherIncomeExpense,
                'tax_expense' => $taxExpense,
                'other_net' => 0,
            ],
            'tax_expense' => $taxExpense,
            'net_income' => $netIncome,
            'net_margin_pct' => $revenue > 0 ? round(($netIncome / $revenue) * 100, 2) : 0,
            'net_profit_margin_pct' => $revenue > 0 ? round(($netIncome / $revenue) * 100, 2) : 0,
        ];

        // 2. Balance Sheet (Laporan Posisi Keuangan / Neraca)
        $cash = max(0, $getVal(['AST_CURR_CASH', 'ASSET_CASH'], 'debit'));
        $ar = max(0, $getVal(['AST_CURR_AR', 'ASSET_AR'], 'debit'));
        $inventory = max(0, $getVal(['AST_CURR_INV', 'ASSET_INVENTORY'], 'debit'));
        $prepaid = max(0, $getVal(['AST_CURR_PREPAID', 'ASSET_PREPAID'], 'debit'));
        $totalCurrentAssets = $cash + $ar + $inventory + $prepaid;

        $fixedAssets = max(0, $getVal(['AST_NONCURR_PPE', 'ASSET_FIXED'], 'debit'));
        $accumDeprec = max(0, $getVal(['AST_NONCURR_ACCDEP', 'ASSET_ACCUM_DEP'], 'credit'));
        $netFixedAssets = max(0, $fixedAssets - $accumDeprec);
        $totalNonCurrentAssets = $netFixedAssets;
        $totalAssets = $totalCurrentAssets + $totalNonCurrentAssets;

        $ap = max(0, $getVal(['LIAB_CURR_AP', 'LIAB_AP'], 'credit'));
        $accruedLiab = max(0, $getVal(['LIAB_CURR_ACCRUED', 'LIAB_ACCRUED'], 'credit'));
        $taxLiab = max(0, $getVal(['TAX_EXPENSE', 'LIAB_TAX'], 'credit'));
        $totalCurrentLiabilities = $ap + $accruedLiab + $taxLiab;

        $longTermDebt = max(0, $getVal(['LIAB_NONCURR_DEBT', 'LIAB_LONG_TERM'], 'credit'));
        $totalLiabilities = $totalCurrentLiabilities + $longTermDebt;

        $paidInCapital = max(0, $getVal(['EQ_CAPITAL', 'EQUITY_CAPITAL'], 'credit'));
        $retainedEarnings = max(0, $getVal(['EQ_RETAINED', 'EQUITY_RETAINED'], 'credit'));
        $currentEarnings = $netIncome; // Balancing with net income
        $totalEquity = $paidInCapital + $retainedEarnings + $currentEarnings;
        $totalLiabAndEquity = $totalLiabilities + $totalEquity;

        $balanceSheet = [
            'assets' => [
                'current' => [
                    'cash' => $cash,
                    'accounts_receivable' => $ar,
                    'inventory' => $inventory,
                    'prepaid' => $prepaid,
                    'prepaid_expenses' => $prepaid,
                    'total' => $totalCurrentAssets,
                ],
                'non_current' => [
                    'fixed_assets' => $fixedAssets,
                    'property_plant_equipment' => $fixedAssets,
                    'accumulated_depreciation' => $accumDeprec,
                    'net_fixed_assets' => $netFixedAssets,
                    'net_ppe' => $netFixedAssets,
                    'intangible_assets' => 0,
                    'total' => $totalNonCurrentAssets,
                ],
                'total_assets' => $totalAssets,
            ],
            'liabilities' => [
                'current' => [
                    'accounts_payable' => $ap,
                    'accrued_expenses' => $accruedLiab,
                    'tax_payable' => $taxLiab,
                    'taxes_payable' => $taxLiab,
                    'total' => $totalCurrentLiabilities,
                ],
                'long_term' => [
                    'debt' => $longTermDebt,
                    'long_term_debt' => $longTermDebt,
                    'total' => $longTermDebt,
                ],
                'non_current' => [
                    'long_term_debt' => $longTermDebt,
                    'total' => $longTermDebt,
                ],
                'total_liabilities' => $totalLiabilities,
            ],
            'equity' => [
                'paid_in_capital' => $paidInCapital,
                'retained_earnings' => $retainedEarnings,
                'retained_earnings_beg' => $retainedEarnings,
                'current_net_income' => $currentEarnings,
                'total_equity' => $totalEquity,
            ],
            'total_liabilities_and_equity' => $totalLiabAndEquity,
            'is_balanced' => abs($totalAssets - $totalLiabAndEquity) < 1.0,
            'variance' => round(abs($totalAssets - $totalLiabAndEquity), 2),
        ];

        // 3. Statement of Changes in Equity
        $equityStatement = [
            'beginning_equity' => $paidInCapital + $retainedEarnings,
            'capital_contributions' => 0,
            'additional_capital' => 0,
            'net_income' => $netIncome,
            'dividends' => 0,
            'ending_equity' => $totalEquity,
        ];

        // 4. Cash Flow Statement (Indirect Method)
        $operatingCashFlow = $netIncome + $opexDeprec;
        $investingCashFlow = 0;
        $financingCashFlow = 0;
        $netCashFlow = $operatingCashFlow + $investingCashFlow + $financingCashFlow;

        $cashFlowStatement = [
            'operating_cash_flow' => $operatingCashFlow,
            'operating_activities' => [
                'net_income' => $netIncome,
                'depreciation' => $opexDeprec,
                'ar_change' => 0,
                'inventory_change' => 0,
                'ap_change' => 0,
                'total' => $operatingCashFlow,
            ],
            'investing_cash_flow' => $investingCashFlow,
            'investing_activities' => [
                'capex' => 0,
                'total' => 0,
            ],
            'financing_cash_flow' => $financingCashFlow,
            'financing_activities' => [
                'debt_change' => 0,
                'total' => 0,
            ],
            'net_cash_flow' => $netCashFlow,
            'net_change_in_cash' => $netCashFlow,
            'beginning_cash' => max(0, $cash - $netCashFlow),
            'ending_cash' => $cash,
        ];

        return [
            'period' => $tb['period'],
            'income_statement' => $incomeStatement,
            'balance_sheet' => $balanceSheet,
            'equity_statement' => $equityStatement,
            'equity_change' => $equityStatement,
            'cash_flow_statement' => $cashFlowStatement,
            'cash_flow' => $cashFlowStatement,
        ];
    }
}
