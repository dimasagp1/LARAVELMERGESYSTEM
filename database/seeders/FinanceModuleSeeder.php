<?php

namespace Database\Seeders;

use App\Core\Models\Department;
use App\Models\User;
use App\Modules\Finance\Models\FinAdjJournal;
use App\Modules\Finance\Models\FinAdjJournalLine;
use App\Modules\Finance\Models\FinCoaAccount;
use App\Modules\Finance\Models\FinCoaMapping;
use App\Modules\Finance\Models\FinFiscalYear;
use App\Modules\Finance\Models\FinFsli;
use App\Modules\Finance\Models\FinGlEntry;
use App\Modules\Finance\Models\FinHeadcount;
use App\Modules\Finance\Models\FinMonthlyBudget;
use App\Modules\Finance\Models\FinPaidInCapital;
use App\Modules\Finance\Models\FinPeriod;
use App\Modules\Finance\Models\FinRatioConfiguration;
use App\Modules\Finance\Models\FinRevenuePlan;
use App\Modules\Finance\Services\SourceReconciliationService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FinanceModuleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Permissions & Roles
        $permissions = [
            'finance.view',
            'finance.aje.create',
            'finance.aje.approve',
            'finance.tb.view',
            'finance.fs.view',
            'finance.budget.manage',
            'finance.ratio.manage',
            'finance.reconcile',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $roles = [
            'finance_maker' => ['finance.view', 'finance.aje.create', 'finance.tb.view', 'finance.fs.view'],
            'finance_checker' => ['finance.view', 'finance.aje.approve', 'finance.tb.view', 'finance.fs.view', 'finance.budget.manage', 'finance.reconcile'],
            'manager_fat' => ['finance.view', 'finance.aje.approve', 'finance.tb.view', 'finance.fs.view', 'finance.budget.manage', 'finance.ratio.manage', 'finance.reconcile'],
            'superadmin' => $permissions,
        ];

        foreach ($roles as $roleName => $perms) {
            $r = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $r->syncPermissions($perms);
        }

        $fatDept = Department::where('code', 'FAT')->first();
        $makerUser = User::firstOrCreate(
            ['email' => 'finance.staff@herbatech.com'],
            [
                'name' => 'Aditya Pratama (Finance Maker)',
                'password' => Hash::make('password123'),
                'department_id' => $fatDept?->id,
                'status' => 'active',
            ]
        );
        $makerUser->assignRole('finance_maker');

        $checkerUser = User::firstOrCreate(
            ['email' => 'finance.manager@herbatech.com'],
            [
                'name' => 'Hendra Gunawan (Finance Checker & Manager)',
                'password' => Hash::make('password123'),
                'department_id' => $fatDept?->id,
                'status' => 'active',
            ]
        );
        $checkerUser->assignRole('finance_checker');

        // 2. Fiscal Year & 12 Periods
        $fy = FinFiscalYear::firstOrCreate(
            ['year' => 2026],
            [
                'name' => 'Tahun Fiskal 2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'is_active' => true,
                'is_closed' => false,
            ]
        );

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        $periodModels = [];
        for ($m = 1; $m <= 12; $m++) {
            $start = Carbon::create(2026, $m, 1)->startOfMonth();
            $end = Carbon::create(2026, $m, 1)->endOfMonth();
            $status = ($m < 11) ? 'closed' : (($m === 11) ? 'open' : 'future');

            $periodModels[$m] = FinPeriod::firstOrCreate(
                [
                    'fiscal_year_id' => $fy->id,
                    'month' => $m,
                    'year' => 2026,
                ],
                [
                    'period_num' => $m,
                    'period_name' => "{$monthNames[$m]} 2026",
                    'start_date' => $start->toDateString(),
                    'end_date' => $end->toDateString(),
                    'status' => $status,
                ]
            );
        }

        $activePeriod = $periodModels[11]; // November 2026

        // 3. FSLI (Financial Statement Line Items)
        $fslis = [
            // Assets
            ['code' => 'AST_CURR_CASH', 'name' => 'Kas dan Setara Kas', 'statement_type' => 'balance_sheet', 'normal_balance' => 'debit', 'sort_order' => 10],
            ['code' => 'AST_CURR_AR', 'name' => 'Piutang Usaha', 'statement_type' => 'balance_sheet', 'normal_balance' => 'debit', 'sort_order' => 20],
            ['code' => 'AST_CURR_INV', 'name' => 'Persediaan Bahan & Barang Jadi', 'statement_type' => 'balance_sheet', 'normal_balance' => 'debit', 'sort_order' => 30],
            ['code' => 'AST_CURR_PREPAID', 'name' => 'Biaya Dibayar Dimuka', 'statement_type' => 'balance_sheet', 'normal_balance' => 'debit', 'sort_order' => 40],
            ['code' => 'AST_NONCURR_PPE', 'name' => 'Aset Tetap & Peralatan Pabrik', 'statement_type' => 'balance_sheet', 'normal_balance' => 'debit', 'sort_order' => 50],
            ['code' => 'AST_NONCURR_ACCDEP', 'name' => 'Akumulasi Penyusutan Aset Tetap', 'statement_type' => 'balance_sheet', 'normal_balance' => 'credit', 'sort_order' => 60],
            ['code' => 'AST_NONCURR_INTANGIBLE', 'name' => 'Aset Tak Berwujud & Paten', 'statement_type' => 'balance_sheet', 'normal_balance' => 'debit', 'sort_order' => 70],
            // Liabilities
            ['code' => 'LIAB_CURR_AP', 'name' => 'Utang Usaha (Trade Accounts Payable)', 'statement_type' => 'balance_sheet', 'normal_balance' => 'credit', 'sort_order' => 110],
            ['code' => 'LIAB_CURR_ACCRUED', 'name' => 'Beban Akrual & Utang Pajak', 'statement_type' => 'balance_sheet', 'normal_balance' => 'credit', 'sort_order' => 120],
            ['code' => 'LIAB_NONCURR_DEBT', 'name' => 'Utang Bank Jangka Panjang', 'statement_type' => 'balance_sheet', 'normal_balance' => 'credit', 'sort_order' => 130],
            // Equity
            ['code' => 'EQ_CAPITAL', 'name' => 'Modal Disetor (Paid-in Capital)', 'statement_type' => 'balance_sheet', 'normal_balance' => 'credit', 'sort_order' => 210],
            ['code' => 'EQ_RETAINED', 'name' => 'Saldo Laba Ditahan (Retained Earnings)', 'statement_type' => 'balance_sheet', 'normal_balance' => 'credit', 'sort_order' => 220],
            // Income Statement
            ['code' => 'REV_SALES', 'name' => 'Pendapatan Penjualan Bersih', 'statement_type' => 'income_statement', 'normal_balance' => 'credit', 'sort_order' => 310],
            ['code' => 'COGS_TOTAL', 'name' => 'Beban Pokok Penjualan (HPP)', 'statement_type' => 'income_statement', 'normal_balance' => 'debit', 'sort_order' => 320],
            ['code' => 'OPEX_SALARIES', 'name' => 'Beban Gaji & Upah Karyawan', 'statement_type' => 'income_statement', 'normal_balance' => 'debit', 'sort_order' => 330],
            ['code' => 'OPEX_MARKETING', 'name' => 'Beban Pemasaran & Penjualan', 'statement_type' => 'income_statement', 'normal_balance' => 'debit', 'sort_order' => 340],
            ['code' => 'OPEX_GA', 'name' => 'Beban Umum & Administrasi', 'statement_type' => 'income_statement', 'normal_balance' => 'debit', 'sort_order' => 350],
            ['code' => 'OPEX_UTILITIES', 'name' => 'Beban Utilitas & Pemeliharaan Pabrik', 'statement_type' => 'income_statement', 'normal_balance' => 'debit', 'sort_order' => 360],
            ['code' => 'OTHER_FIN_EXP', 'name' => 'Beban Keuangan & Bunga Bank', 'statement_type' => 'income_statement', 'normal_balance' => 'debit', 'sort_order' => 370],
            ['code' => 'TAX_EXPENSE', 'name' => 'Taksiran Beban Pajak Penghasilan (PPh)', 'statement_type' => 'income_statement', 'normal_balance' => 'debit', 'sort_order' => 380],
        ];

        $fsliMap = [];
        foreach ($fslis as $f) {
            $fsliMap[$f['code']] = FinFsli::updateOrCreate(['code' => $f['code']], $f);
        }

        // 4. Chart of Accounts (COA)
        $accounts = [
            ['code' => '1101', 'name' => 'Kas Operasional & Bank BCA', 'type' => 'asset', 'balance' => 'debit', 'fsli' => 'AST_CURR_CASH'],
            ['code' => '1102', 'name' => 'Kas Kecil (Petty Cash FAT)', 'type' => 'asset', 'balance' => 'debit', 'fsli' => 'AST_CURR_CASH'],
            ['code' => '1201', 'name' => 'Piutang Usaha Distributor & Apotek', 'type' => 'asset', 'balance' => 'debit', 'fsli' => 'AST_CURR_AR'],
            ['code' => '1301', 'name' => 'Persediaan Bahan Baku Herbal (Raw Material)', 'type' => 'asset', 'balance' => 'debit', 'fsli' => 'AST_CURR_INV'],
            ['code' => '1302', 'name' => 'Persediaan Produk Jadi (Finished Goods)', 'type' => 'asset', 'balance' => 'debit', 'fsli' => 'AST_CURR_INV'],
            ['code' => '1401', 'name' => 'Asuransi & Biaya Sewa Dibayar Dimuka', 'type' => 'asset', 'balance' => 'debit', 'fsli' => 'AST_CURR_PREPAID'],
            ['code' => '1501', 'name' => 'Mesin Ekstraksi & Bangunan Pabrik', 'type' => 'asset', 'balance' => 'debit', 'fsli' => 'AST_NONCURR_PPE'],
            ['code' => '1599', 'name' => 'Akumulasi Penyusutan Mesin & Bangunan', 'type' => 'asset', 'balance' => 'credit', 'fsli' => 'AST_NONCURR_ACCDEP'],
            ['code' => '2101', 'name' => 'Utang Usaha Supplier Bahan Baku', 'type' => 'liability', 'balance' => 'credit', 'fsli' => 'LIAB_CURR_AP'],
            ['code' => '2102', 'name' => 'Utang Beban Akrual & Utang Pajak PPh 21/23', 'type' => 'liability', 'balance' => 'credit', 'fsli' => 'LIAB_CURR_ACCRUED'],
            ['code' => '2201', 'name' => 'Utang Bank Investasi Jangka Panjang Mandiri', 'type' => 'liability', 'balance' => 'credit', 'fsli' => 'LIAB_NONCURR_DEBT'],
            ['code' => '3101', 'name' => 'Modal Saham Ditempatkan & Disetor', 'type' => 'equity', 'balance' => 'credit', 'fsli' => 'EQ_CAPITAL'],
            ['code' => '3201', 'name' => 'Saldo Laba Ditahan Periode Lalu', 'type' => 'equity', 'balance' => 'credit', 'fsli' => 'EQ_RETAINED'],
            ['code' => '4101', 'name' => 'Penjualan Produk Herbal Standar & Ekstrak', 'type' => 'income', 'balance' => 'credit', 'fsli' => 'REV_SALES'],
            ['code' => '5101', 'name' => 'Beban Pokok Produksi & Pembelian Bahan', 'type' => 'expense', 'balance' => 'debit', 'fsli' => 'COGS_TOTAL'],
            ['code' => '6101', 'name' => 'Gaji Pokok, Tunjangan & Lembur Karyawan', 'type' => 'expense', 'balance' => 'debit', 'fsli' => 'OPEX_SALARIES'],
            ['code' => '6201', 'name' => 'Beban Iklan, Promosi & Sampel Marketing', 'type' => 'expense', 'balance' => 'debit', 'fsli' => 'OPEX_MARKETING'],
            ['code' => '6301', 'name' => 'Beban Perlengkapan Kantor, Legal & Audit', 'type' => 'expense', 'balance' => 'debit', 'fsli' => 'OPEX_GA'],
            ['code' => '6401', 'name' => 'Beban Listrik PLN, Air & Pemeliharaan Mesin', 'type' => 'expense', 'balance' => 'debit', 'fsli' => 'OPEX_UTILITIES'],
            ['code' => '7101', 'name' => 'Beban Bunga Kredit Modal Kerja', 'type' => 'expense', 'balance' => 'debit', 'fsli' => 'OTHER_FIN_EXP'],
            ['code' => '8101', 'name' => 'Taksiran Pajak Penghasilan Badan', 'type' => 'expense', 'balance' => 'debit', 'fsli' => 'TAX_EXPENSE'],
        ];

        $accountModels = [];
        foreach ($accounts as $acc) {
            $fsliId = $fsliMap[$acc['fsli']]->id ?? null;
            $accountModels[$acc['code']] = FinCoaAccount::updateOrCreate(
                ['account_code' => $acc['code']],
                [
                    'account_name' => $acc['name'],
                    'account_type' => $acc['type'],
                    'normal_balance' => $acc['balance'],
                    'fsli_id' => $fsliId,
                    'is_active' => true,
                ]
            );

            // Mapping to Odoo mirror
            FinCoaMapping::updateOrCreate(
                ['odoo_account_code' => $acc['code']],
                [
                    'fsli_id' => $fsliId,
                    'odoo_account_name' => $acc['name'],
                    'department_id' => $fatDept?->id,
                    'is_active' => true,
                ]
            );
        }

        // 5. General Ledger (GL) Entries for Period 11 (November 2026)
        // Strictly balanced: Debits = 8,760,000,000 | Credits = 8,760,000,000
        $glEntries = [
            ['acc' => '4101', 'fsli' => 'REV_SALES', 'debit' => 0, 'credit' => 1850000000, 'move' => 'INV/2026/11/0001', 'desc' => 'Akumulasi Penjualan Produk Herbal Nov 2026'],
            ['acc' => '5101', 'fsli' => 'COGS_TOTAL', 'debit' => 830000000, 'credit' => 0, 'move' => 'COGS/2026/11/0001', 'desc' => 'Beban Pokok Penjualan (HPP) Periode Nov 2026'],
            ['acc' => '6101', 'fsli' => 'OPEX_SALARIES', 'debit' => 190000000, 'credit' => 0, 'move' => 'PAY/2026/11/0001', 'desc' => 'Payroll & Gaji Karyawan Nov 2026'],
            ['acc' => '6201', 'fsli' => 'OPEX_MARKETING', 'debit' => 80000000, 'credit' => 0, 'move' => 'MKT/2026/11/0001', 'desc' => 'Beban Promosi & Pameran Farmasi'],
            ['acc' => '6301', 'fsli' => 'OPEX_GA', 'debit' => 60000000, 'credit' => 0, 'move' => 'GA/2026/11/0001', 'desc' => 'Beban Operasional Kantor & Konsultan'],
            ['acc' => '6401', 'fsli' => 'OPEX_UTILITIES', 'debit' => 50000000, 'credit' => 0, 'move' => 'UTL/2026/11/0001', 'desc' => 'Beban Listrik PLN & Air Pabrik Sukoharjo'],
            ['acc' => '1101', 'fsli' => 'AST_CURR_CASH', 'debit' => 650000000, 'credit' => 0, 'move' => 'BNK/2026/11/0001', 'desc' => 'Saldo Kas dan Bank Akhir Nov 2026'],
            ['acc' => '1201', 'fsli' => 'AST_CURR_AR', 'debit' => 550000000, 'credit' => 0, 'move' => 'AR/2026/11/0001', 'desc' => 'Saldo Piutang Dagang Nov 2026'],
            ['acc' => '1301', 'fsli' => 'AST_CURR_INV', 'debit' => 1200000000, 'credit' => 0, 'move' => 'INV/2026/11/0002', 'desc' => 'Nilai Persediaan Fisik Bahan & Barang Jadi'],
            ['acc' => '1401', 'fsli' => 'AST_CURR_PREPAID', 'debit' => 150000000, 'credit' => 0, 'move' => 'PPD/2026/11/0001', 'desc' => 'Asuransi Pabrik Dibayar Dimuka'],
            ['acc' => '1501', 'fsli' => 'AST_NONCURR_PPE', 'debit' => 5000000000, 'credit' => 0, 'move' => 'AST/2026/11/0001', 'desc' => 'Aset Tetap Mesin & Fasilitas Produksi Sukoharjo'],
            ['acc' => '1599', 'fsli' => 'AST_NONCURR_ACCDEP', 'debit' => 0, 'credit' => 800000000, 'move' => 'DEP/2026/11/0001', 'desc' => 'Akumulasi Penyusutan Aset Tetap Pabrik'],
            ['acc' => '2101', 'fsli' => 'LIAB_CURR_AP', 'debit' => 0, 'credit' => 420000000, 'move' => 'AP/2026/11/0001', 'desc' => 'Utang Usaha Supplier Bahan Baku'],
            ['acc' => '2102', 'fsli' => 'LIAB_CURR_ACCRUED', 'debit' => 0, 'credit' => 180000000, 'move' => 'ACR/2026/11/0001', 'desc' => 'Akrual Beban Akhir Periode'],
            ['acc' => '2201', 'fsli' => 'LIAB_NONCURR_DEBT', 'debit' => 0, 'credit' => 1000000000, 'move' => 'LON/2026/11/0001', 'desc' => 'Pinjaman Investasi Mesin Mandiri'],
            ['acc' => '3101', 'fsli' => 'EQ_CAPITAL', 'debit' => 0, 'credit' => 4000000000, 'move' => 'CAP/2026/11/0001', 'desc' => 'Modal Saham Disetor Pendiri'],
            ['acc' => '3201', 'fsli' => 'EQ_RETAINED', 'debit' => 0, 'credit' => 510000000, 'move' => 'RET/2026/11/0001', 'desc' => 'Saldo Laba Ditahan Akumulasi'],
        ];

        FinGlEntry::where('period_id', $activePeriod->id)->delete();

        foreach ($glEntries as $entry) {
            $fsliId = $fsliMap[$entry['fsli']]->id;
            $signed = $entry['debit'] > 0 ? (float) $entry['debit'] : -(float) $entry['credit'];

            FinGlEntry::create([
                'period_id' => $activePeriod->id,
                'transaction_date' => '2026-11-20',
                'move_id' => $entry['move'],
                'doc_ref' => $entry['move'],
                'account_code' => $entry['acc'],
                'account_name' => $accountModels[$entry['acc']]->account_name,
                'fsli_id' => $fsliId,
                'department_id' => $fatDept?->id,
                'debit' => $entry['debit'],
                'credit' => $entry['credit'],
                'signed_amount' => $signed,
                'description' => $entry['desc'],
                'is_synced' => true,
            ]);
        }

        // 6. Adjusting Journal Entries (AJE) Maker-Checker
        // AJE 1: Approved -> affects Trial Balance (Debet 15M, Credit 15M)
        $ajeApproved = FinAdjJournal::updateOrCreate(
            ['journal_number' => 'AJE-202611-0001'],
            [
                'fiscal_year_id' => $fy->id,
                'period_id' => $activePeriod->id,
                'journal_date' => '2026-11-25',
                'description' => 'Akrual tagihan listrik pabrik PLN Sukoharjo bulan berjalan yang belum terbit faktur.',
                'category' => 'accrual',
                'total_debit' => 15000000,
                'total_credit' => 15000000,
                'status' => 'approved',
                'created_by' => $makerUser->id,
                'approved_by' => $checkerUser->id,
                'approved_at' => Carbon::now()->subHours(4),
            ]
        );

        FinAdjJournalLine::where('adj_journal_id', $ajeApproved->id)->delete();
        FinAdjJournalLine::create([
            'adj_journal_id' => $ajeApproved->id,
            'coa_account_id' => $accountModels['6401']->id,
            'account_code' => '6401',
            'account_name' => 'Beban Listrik PLN, Air & Pemeliharaan Mesin',
            'fsli_id' => $fsliMap['OPEX_UTILITIES']->id,
            'department_id' => $fatDept?->id,
            'debit' => 15000000,
            'credit' => 0,
            'description' => 'Beban listrik akrual pabrik Sukoharjo',
        ]);
        FinAdjJournalLine::create([
            'adj_journal_id' => $ajeApproved->id,
            'coa_account_id' => $accountModels['2102']->id,
            'account_code' => '2102',
            'account_name' => 'Utang Beban Akrual & Utang Pajak PPh 21/23',
            'fsli_id' => $fsliMap['LIAB_CURR_ACCRUED']->id,
            'department_id' => $fatDept?->id,
            'debit' => 0,
            'credit' => 15000000,
            'description' => 'Utang beban akrual listrik PLN',
        ]);

        // AJE 2: Submitted -> in Checker's review queue on Overview
        $ajeSubmitted = FinAdjJournal::updateOrCreate(
            ['journal_number' => 'AJE-202611-0002'],
            [
                'fiscal_year_id' => $fy->id,
                'period_id' => $activePeriod->id,
                'journal_date' => '2026-11-28',
                'description' => 'Penyesuaian lembur shift ekstraksi batch kurkumin 500 kg akhir pekan.',
                'category' => 'accrual',
                'total_debit' => 8500000,
                'total_credit' => 8500000,
                'status' => 'submitted',
                'created_by' => $makerUser->id,
            ]
        );

        FinAdjJournalLine::where('adj_journal_id', $ajeSubmitted->id)->delete();
        FinAdjJournalLine::create([
            'adj_journal_id' => $ajeSubmitted->id,
            'coa_account_id' => $accountModels['6101']->id,
            'account_code' => '6101',
            'account_name' => 'Gaji Pokok, Tunjangan & Lembur Karyawan',
            'fsli_id' => $fsliMap['OPEX_SALARIES']->id,
            'department_id' => $fatDept?->id,
            'debit' => 8500000,
            'credit' => 0,
            'description' => 'Akrual lembur shift ekstraksi',
        ]);
        FinAdjJournalLine::create([
            'adj_journal_id' => $ajeSubmitted->id,
            'coa_account_id' => $accountModels['2102']->id,
            'account_code' => '2102',
            'account_name' => 'Utang Beban Akrual & Utang Pajak PPh 21/23',
            'fsli_id' => $fsliMap['LIAB_CURR_ACCRUED']->id,
            'department_id' => $fatDept?->id,
            'debit' => 0,
            'credit' => 8500000,
            'description' => 'Utang beban lembur karyawan',
        ]);

        // 7. Department Monthly Budgets (11 Departments)
        $allDepts = Department::all();
        $budgetAllocations = [
            'FAT' => ['initial' => 45000000, 'realized' => 32000000, 'booked' => 5000000],
            'GA' => ['initial' => 50000000, 'realized' => 38000000, 'booked' => 4000000],
            'HR' => ['initial' => 35000000, 'realized' => 22000000, 'booked' => 2000000],
            'PROC' => ['initial' => 60000000, 'realized' => 44000000, 'booked' => 8000000],
            'PROD' => ['initial' => 90000000, 'realized' => 68000000, 'booked' => 12000000],
            'QA' => ['initial' => 30000000, 'realized' => 18000000, 'booked' => 3000000],
            'QC' => ['initial' => 40000000, 'realized' => 26000000, 'booked' => 4000000],
            'RND' => ['initial' => 55000000, 'realized' => 39000000, 'booked' => 6000000],
            'SCM' => ['initial' => 45000000, 'realized' => 29000000, 'booked' => 5000000],
        ];

        foreach ($allDepts as $dept) {
            $alloc = $budgetAllocations[$dept->code] ?? ['initial' => 30000000, 'realized' => 18000000, 'booked' => 2000000];
            FinMonthlyBudget::updateOrCreate(
                [
                    'period_id' => $activePeriod->id,
                    'department_id' => $dept->id,
                ],
                [
                    'fiscal_year_id' => $fy->id,
                    'initial_budget' => $alloc['initial'],
                    'realized_amount' => $alloc['realized'],
                    'booked_amount' => $alloc['booked'],
                ]
            );
        }

        // 8. Revenue Plan for 2026
        FinRevenuePlan::updateOrCreate(
            [
                'fiscal_year_id' => $fy->id,
                'month' => 11,
            ],
            [
                'target_revenue' => 1500000000, // Target 1.5 Billion
                'notes' => 'Target pendapatan bulanan Herbaltech Q4',
            ]
        );

        // 9. Non-Odoo Factors (Headcount & Paid-in Capital)
        FinHeadcount::updateOrCreate(
            ['period_id' => $activePeriod->id],
            [
                'total_headcount' => 48,
                'direct_labor_count' => 32,
                'working_hours' => 7680, // 48 * 160 hours
            ]
        );

        FinPaidInCapital::updateOrCreate(
            ['period_id' => $activePeriod->id],
            [
                'amount' => 5000000000, // 5 Billion Rp
                'effective_date' => '2026-01-01',
            ]
        );

        // 10. 19 Balanced Scorecard (BSC) Configurations
        $ratioConfigs = [
            // Profitability (30%)
            ['ratio_code' => 'GPM', 'name' => 'Gross Profit Margin', 'pillar' => 'profitability', 'target_value' => 55.0, 'unit' => '%', 'polarity' => 'higher_is_better', 'weight_pct' => 8.0],
            ['ratio_code' => 'NPM', 'name' => 'Net Profit Margin', 'pillar' => 'profitability', 'target_value' => 20.0, 'unit' => '%', 'polarity' => 'higher_is_better', 'weight_pct' => 8.0],
            ['ratio_code' => 'ROA', 'name' => 'Return on Assets', 'pillar' => 'profitability', 'target_value' => 12.0, 'unit' => '%', 'polarity' => 'higher_is_better', 'weight_pct' => 7.0],
            ['ratio_code' => 'ROE', 'name' => 'Return on Equity', 'pillar' => 'profitability', 'target_value' => 20.0, 'unit' => '%', 'polarity' => 'higher_is_better', 'weight_pct' => 7.0],
            // Activity (25%)
            ['ratio_code' => 'ITO', 'name' => 'Inventory Turnover Ratio', 'pillar' => 'activity', 'target_value' => 6.0, 'unit' => 'x', 'polarity' => 'higher_is_better', 'weight_pct' => 5.0],
            ['ratio_code' => 'ATO', 'name' => 'Asset Turnover Ratio', 'pillar' => 'activity', 'target_value' => 1.5, 'unit' => 'x', 'polarity' => 'higher_is_better', 'weight_pct' => 4.0],
            ['ratio_code' => 'ARTO', 'name' => 'Accounts Receivable Turnover', 'pillar' => 'activity', 'target_value' => 8.0, 'unit' => 'x', 'polarity' => 'higher_is_better', 'weight_pct' => 4.0],
            ['ratio_code' => 'DIO', 'name' => 'Days Inventory Outstanding', 'pillar' => 'activity', 'target_value' => 60.0, 'unit' => 'Hari', 'polarity' => 'lower_is_better', 'weight_pct' => 4.0],
            ['ratio_code' => 'DSO', 'name' => 'Days Sales Outstanding', 'pillar' => 'activity', 'target_value' => 45.0, 'unit' => 'Hari', 'polarity' => 'lower_is_better', 'weight_pct' => 4.0],
            ['ratio_code' => 'DPO', 'name' => 'Days Payable Outstanding', 'pillar' => 'activity', 'target_value' => 45.0, 'unit' => 'Hari', 'polarity' => 'higher_is_better', 'weight_pct' => 4.0],
            // Productivity (20%)
            ['ratio_code' => 'PROD_EMP', 'name' => 'Revenue per Employee', 'pillar' => 'productivity', 'target_value' => 35000000, 'unit' => 'Rp', 'polarity' => 'higher_is_better', 'weight_pct' => 5.0],
            ['ratio_code' => 'PROD_HR', 'name' => 'Revenue per Working Hour', 'pillar' => 'productivity', 'target_value' => 200000, 'unit' => 'Rp', 'polarity' => 'higher_is_better', 'weight_pct' => 5.0],
            ['ratio_code' => 'PROD_LABOR_COST', 'name' => 'Revenue to Labor Cost', 'pillar' => 'productivity', 'target_value' => 8.0, 'unit' => 'x', 'polarity' => 'higher_is_better', 'weight_pct' => 5.0],
            ['ratio_code' => 'CPR', 'name' => 'Corporate Productivity Ratio (CPR)', 'pillar' => 'productivity', 'target_value' => 25.0, 'unit' => '%', 'polarity' => 'higher_is_better', 'weight_pct' => 5.0],
            // Liquidity (15%)
            ['ratio_code' => 'CR', 'name' => 'Current Ratio', 'pillar' => 'liquidity', 'target_value' => 150.0, 'unit' => '%', 'polarity' => 'higher_is_better', 'weight_pct' => 5.0],
            ['ratio_code' => 'CASH_R', 'name' => 'Cash Ratio', 'pillar' => 'liquidity', 'target_value' => 50.0, 'unit' => '%', 'polarity' => 'higher_is_better', 'weight_pct' => 5.0],
            ['ratio_code' => 'QR', 'name' => 'Quick Ratio (Acid Test)', 'pillar' => 'liquidity', 'target_value' => 100.0, 'unit' => '%', 'polarity' => 'higher_is_better', 'weight_pct' => 5.0],
            // Solvency (10%)
            ['ratio_code' => 'DER', 'name' => 'Debt to Equity Ratio', 'pillar' => 'solvency', 'target_value' => 0.70, 'unit' => 'x', 'polarity' => 'lower_is_better', 'weight_pct' => 5.0],
            ['ratio_code' => 'DAR', 'name' => 'Debt to Asset Ratio', 'pillar' => 'solvency', 'target_value' => 0.40, 'unit' => 'x', 'polarity' => 'lower_is_better', 'weight_pct' => 5.0],
        ];

        foreach ($ratioConfigs as $rc) {
            FinRatioConfiguration::updateOrCreate(['ratio_code' => $rc['ratio_code']], $rc);
        }

        // 11. Run 9-Point Reconciliation Audit
        $reconcileService = app(SourceReconciliationService::class);
        $reconcileService->runReconciliation($activePeriod->id);
    }
}
