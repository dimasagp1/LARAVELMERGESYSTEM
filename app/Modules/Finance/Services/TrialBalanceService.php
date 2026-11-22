<?php

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\FinAdjJournalLine;
use App\Modules\Finance\Models\FinCoaAccount;
use App\Modules\Finance\Models\FinFsli;
use App\Modules\Finance\Models\FinGlEntry;
use App\Modules\Finance\Models\FinPeriod;

class TrialBalanceService
{
    /**
     * Compute 4-Column Working Sheet (Trial Balance).
     *
     * 1. Unadjusted Trial Balance (from Odoo GL Entries)
     * 2. Adjustments (from Approved AJE entries)
     * 3. Adjusted Trial Balance (Unadjusted + Adjustments)
     * 4. Financial Statements Categorization (FSLI Mapping)
     */
    public function getTrialBalance(int $periodId): array
    {
        $period = FinPeriod::find($periodId);
        $periodName = $period?->period_name ?? 'November 2026';
        $periodYear = $period?->year ?? 2026;
        $periodMonth = $period?->month ?? 11;
        $periodStatus = $period?->status ?? 'open';

        $fslis = FinFsli::orderBy('sort_order')->get();
        $accounts = FinCoaAccount::all()->keyBy('account_code');

        // Unadjusted: raw GL entries synced from Odoo
        $glEntries = FinGlEntry::where('period_id', $periodId)->get();

        // Adjustments: only APPROVED AJEs for this period
        $ajeLines = FinAdjJournalLine::whereHas('adjJournal', function ($q) use ($periodId) {
            $q->where('period_id', $periodId)
                ->where('status', 'approved');
        })->get();

        $rows = [];
        $totalUnadjDebit = 0;
        $totalUnadjCredit = 0;
        $totalAdjDebit = 0;
        $totalAdjCredit = 0;
        $totalAdjustedDebit = 0;
        $totalAdjustedCredit = 0;

        foreach ($fslis as $fsli) {
            $fsliGl = $glEntries->where('fsli_id', $fsli->id);
            $fsliAje = $ajeLines->where('fsli_id', $fsli->id);

            // Group by account
            $accountGroups = [];
            $allAccountCodes = $fsliGl->pluck('account_code')->merge($fsliAje->pluck('account_code'))->unique();

            $fsliUnadjDebit = 0;
            $fsliUnadjCredit = 0;
            $fsliAdjDebit = 0;
            $fsliAdjCredit = 0;

            foreach ($allAccountCodes as $code) {
                $accGl = $fsliGl->where('account_code', $code);
                $accAje = $fsliAje->where('account_code', $code);
                $accMeta = $accounts->get($code);

                $unadjDeb = (float) $accGl->sum('debit');
                $unadjCred = (float) $accGl->sum('credit');
                $adjDeb = (float) $accAje->sum('debit');
                $adjCred = (float) $accAje->sum('credit');

                $fsliUnadjDebit += $unadjDeb;
                $fsliUnadjCredit += $unadjCred;
                $fsliAdjDebit += $adjDeb;
                $fsliAdjCredit += $adjCred;

                // Net Adjusted calculation based on normal balance
                $netDebit = ($unadjDeb + $adjDeb) - ($unadjCred + $adjCred);
                $finalDebit = $netDebit > 0 ? $netDebit : 0;
                $finalCredit = $netDebit < 0 ? abs($netDebit) : 0;

                $accountGroups[] = [
                    'account_code' => $code,
                    'account_name' => $accMeta?->account_name ?? ($accGl->first()?->account_name ?? 'Akun '.$code),
                    'unadjusted_debit' => $unadjDeb,
                    'unadjusted_credit' => $unadjCred,
                    'adj_debit' => $adjDeb,
                    'adj_credit' => $adjCred,
                    'adjusted_debit' => $finalDebit,
                    'adjusted_credit' => $finalCredit,
                    'transactions_count' => $accGl->count() + $accAje->count(),
                ];
            }

            // Calculate FSLI net adjusted
            $fsliNet = ($fsliUnadjDebit + $fsliAdjDebit) - ($fsliUnadjCredit + $fsliAdjCredit);
            $fsliAdjustedDebit = $fsliNet > 0 ? $fsliNet : 0;
            $fsliAdjustedCredit = $fsliNet < 0 ? abs($fsliNet) : 0;

            $totalUnadjDebit += $fsliUnadjDebit;
            $totalUnadjCredit += $fsliUnadjCredit;
            $totalAdjDebit += $fsliAdjDebit;
            $totalAdjCredit += $fsliAdjCredit;
            $totalAdjustedDebit += $fsliAdjustedDebit;
            $totalAdjustedCredit += $fsliAdjustedCredit;

            $rows[] = [
                'fsli_id' => $fsli->id,
                'code' => $fsli->code,
                'name' => $fsli->name,
                'statement_type' => $fsli->statement_type,
                'normal_balance' => $fsli->normal_balance,
                'unadjusted_debit' => $fsliUnadjDebit,
                'unadjusted_credit' => $fsliUnadjCredit,
                'adj_debit' => $fsliAdjDebit,
                'adj_credit' => $fsliAdjCredit,
                'adjusted_debit' => $fsliAdjustedDebit,
                'adjusted_credit' => $fsliAdjustedCredit,
                'accounts' => $accountGroups,
            ];
        }

        $variance = abs($totalAdjustedDebit - $totalAdjustedCredit);
        $isBalanced = $variance < 1.0;

        $lines = array_map(function ($r) {
            return [
                'fsli_id' => $r['fsli_id'],
                'code' => $r['code'],
                'name' => $r['name'],
                'statement_type' => $r['statement_type'],
                'unadjusted' => ['debit' => $r['unadjusted_debit'], 'credit' => $r['unadjusted_credit']],
                'adjustments' => ['debit' => $r['adj_debit'], 'credit' => $r['adj_credit']],
                'adjusted' => ['debit' => $r['adjusted_debit'], 'credit' => $r['adjusted_credit']],
            ];
        }, $rows);

        return [
            'period' => [
                'id' => $periodId,
                'name' => $periodName,
                'year' => $periodYear,
                'month' => $periodMonth,
                'status' => $periodStatus,
            ],
            'is_balanced' => $isBalanced,
            'total_unadjusted_debit' => $totalUnadjDebit,
            'total_unadjusted_credit' => $totalUnadjCredit,
            'total_adjustment_debit' => $totalAdjDebit,
            'total_adjustment_credit' => $totalAdjCredit,
            'total_adjusted_debit' => $totalAdjustedDebit,
            'total_adjusted_credit' => $totalAdjustedCredit,
            'lines' => $lines,
            'rows' => $rows,
            'totals' => [
                'unadjusted_debit' => $totalUnadjDebit,
                'unadjusted_credit' => $totalUnadjCredit,
                'adj_debit' => $totalAdjDebit,
                'adj_credit' => $totalAdjCredit,
                'adjusted_debit' => $totalAdjustedDebit,
                'adjusted_credit' => $totalAdjustedCredit,
                'variance' => round($variance, 2),
                'is_balanced' => $isBalanced,
            ],
        ];
    }

    /**
     * Level 2 & 3 Drilldown: underlying accounts and vouchers for an FSLI.
     */
    public function getDrilldownData(int $periodId, int $fsliId): array
    {
        $fsli = FinFsli::findOrFail($fsliId);
        $accounts = FinCoaAccount::where('fsli_id', $fsliId)->get();

        $glEntries = FinGlEntry::with('department')
            ->where('period_id', $periodId)
            ->where('fsli_id', $fsliId)
            ->get();

        $ajeLines = FinAdjJournalLine::whereHas('adjJournal', fn ($q) => $q->where('period_id', $periodId)->where('status', 'approved'))
            ->where('fsli_id', $fsliId)
            ->with(['adjJournal', 'department'])
            ->get();

        $accountRows = [];
        foreach ($accounts as $acc) {
            $aGl = $glEntries->where('account_code', $acc->account_code);
            $aAje = $ajeLines->where('account_code', $acc->account_code);
            $accountRows[] = [
                'id' => $acc->id,
                'code' => $acc->account_code,
                'name' => $acc->account_name,
                'debit' => (float) $aGl->sum('debit') + (float) $aAje->sum('debit'),
                'credit' => (float) $aGl->sum('credit') + (float) $aAje->sum('credit'),
            ];
        }

        $entryRows = [];
        foreach ($glEntries as $gl) {
            $entryRows[] = [
                'id' => $gl->id,
                'move_number' => $gl->move_id,
                'reference' => $gl->doc_ref,
                'date' => $gl->transaction_date ? (is_string($gl->transaction_date) ? $gl->transaction_date : $gl->transaction_date->format('Y-m-d')) : null,
                'account_code' => $gl->account_code,
                'account_name' => $gl->account_name,
                'department' => $gl->department,
                'debit' => (float) $gl->debit,
                'credit' => (float) $gl->credit,
                'description' => $gl->description,
            ];
        }

        foreach ($ajeLines as $aje) {
            $entryRows[] = [
                'id' => 'aje_'.$aje->id,
                'move_number' => $aje->adjJournal?->journal_number,
                'reference' => 'AJE',
                'date' => $aje->adjJournal?->journal_date ? (is_string($aje->adjJournal->journal_date) ? $aje->adjJournal->journal_date : $aje->adjJournal->journal_date->format('Y-m-d')) : null,
                'account_code' => $aje->account_code,
                'account_name' => $aje->account_name,
                'department' => $aje->department,
                'debit' => (float) $aje->debit,
                'credit' => (float) $aje->credit,
                'description' => $aje->description,
            ];
        }

        return [
            'fsli' => $fsli,
            'accounts' => $accountRows,
            'entries' => $entryRows,
        ];
    }
}
