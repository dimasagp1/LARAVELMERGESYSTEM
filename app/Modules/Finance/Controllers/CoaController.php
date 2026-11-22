<?php

namespace App\Modules\Finance\Controllers;

use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Modules\Finance\Models\FinAdjJournalLine;
use App\Modules\Finance\Models\FinCoaAccount;
use App\Modules\Finance\Models\FinFsli;
use App\Modules\Finance\Models\FinGlEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CoaController extends Controller
{
    public function __construct(
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Display a listing of the Chart of Accounts (COA).
     */
    public function index(Request $request): Response
    {
        $search = trim((string) $request->input('search', ''));
        $type = $request->input('type', 'all');
        $status = $request->input('status', 'all');

        $query = FinCoaAccount::query()
            ->with(['fsli', 'parent'])
            ->orderBy('account_code');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('account_code', 'like', "%{$search}%")
                    ->orWhere('account_name', 'like', "%{$search}%");
            });
        }

        if ($type !== 'all' && in_array($type, ['asset', 'liability', 'equity', 'income', 'expense'])) {
            $query->where('account_type', $type);
        }

        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $allAccounts = $query->get();

        // Calculate running balance per account from GL entries + approved adjustments
        $glSums = FinGlEntry::query()
            ->select('account_code', DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
            ->groupBy('account_code')
            ->get()
            ->keyBy('account_code');

        $adjSums = FinAdjJournalLine::query()
            ->whereHas('adjJournal', fn ($q) => $q->where('status', 'approved'))
            ->select('account_code', DB::raw('SUM(debit) as total_debit'), DB::raw('SUM(credit) as total_credit'))
            ->groupBy('account_code')
            ->get()
            ->keyBy('account_code');

        $accountsWithBalances = $allAccounts->map(function (FinCoaAccount $account) use ($glSums, $adjSums) {
            $gl = $glSums->get($account->account_code);
            $adj = $adjSums->get($account->account_code);

            $debit = (float) (($gl?->total_debit ?? 0) + ($adj?->total_debit ?? 0));
            $credit = (float) (($gl?->total_credit ?? 0) + ($adj?->total_credit ?? 0));
            $opening = (float) $account->opening_balance;

            if ($account->normal_balance === 'debit') {
                $currentBalance = $opening + $debit - $credit;
            } else {
                $currentBalance = $opening + $credit - $debit;
            }

            return [
                'id' => $account->id,
                'account_code' => $account->account_code,
                'account_name' => $account->account_name,
                'account_type' => $account->account_type,
                'normal_balance' => $account->normal_balance,
                'is_header' => (bool) $account->is_header,
                'is_active' => (bool) $account->is_active,
                'parent_id' => $account->parent_id,
                'parent_name' => $account->parent?->account_name,
                'fsli_id' => $account->fsli_id,
                'fsli_name' => $account->fsli?->name,
                'fsli_code' => $account->fsli?->code,
                'opening_balance' => $opening,
                'total_debit' => $debit,
                'total_credit' => $credit,
                'current_balance' => $currentBalance,
                'description' => $account->description,
            ];
        });

        // Summary cards
        $totalAssets = $accountsWithBalances->where('account_type', 'asset')->sum('current_balance');
        $totalLiabilities = $accountsWithBalances->where('account_type', 'liability')->sum('current_balance');
        $totalEquity = $accountsWithBalances->where('account_type', 'equity')->sum('current_balance');
        $totalRevenue = $accountsWithBalances->where('account_type', 'income')->sum('current_balance');
        $totalExpenses = $accountsWithBalances->where('account_type', 'expense')->sum('current_balance');

        return Inertia::render('Modules/Finance/Coa/Index', [
            'module' => [
                'name' => 'Finance & Budgeting',
                'slug' => 'finance',
                'color_theme' => 'emerald',
                'menus' => $this->moduleManager->getModuleMenus('finance'),
            ],
            'accounts' => $accountsWithBalances->values(),
            'fslis' => FinFsli::orderBy('sort_order')->get(['id', 'code', 'name', 'statement_type']),
            'headers' => FinCoaAccount::where('is_header', true)->orderBy('account_code')->get(['id', 'account_code', 'account_name', 'account_type']),
            'summary' => [
                'total_accounts' => $allAccounts->count(),
                'active_accounts' => $allAccounts->where('is_active', true)->count(),
                'total_assets' => $totalAssets,
                'total_liabilities' => $totalLiabilities,
                'total_equity' => $totalEquity,
                'total_revenue' => $totalRevenue,
                'total_expenses' => $totalExpenses,
            ],
            'filters' => [
                'search' => $search,
                'type' => $type,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Store a newly created account in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:50|unique:fin_coa_accounts,account_code',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,income,expense',
            'normal_balance' => 'required|in:debit,credit',
            'fsli_id' => 'required|exists:fin_fsli,id',
            'parent_id' => 'nullable|exists:fin_coa_accounts,id',
            'is_header' => 'boolean',
            'opening_balance' => 'nullable|numeric',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['is_header'] = (bool) ($request->input('is_header', false));
        $validated['opening_balance'] = (float) ($request->input('opening_balance', 0));
        $validated['is_active'] = true;

        FinCoaAccount::create($validated);

        return redirect()->back()->with('success', "Akun COA {$validated['account_code']} - {$validated['account_name']} berhasil ditambahkan.");
    }

    /**
     * Update the specified account in storage.
     */
    public function update(Request $request, FinCoaAccount $coa): RedirectResponse
    {
        $validated = $request->validate([
            'account_code' => "required|string|max:50|unique:fin_coa_accounts,account_code,{$coa->id}",
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,income,expense',
            'normal_balance' => 'required|in:debit,credit',
            'fsli_id' => 'required|exists:fin_fsli,id',
            'parent_id' => 'nullable|exists:fin_coa_accounts,id',
            'is_header' => 'boolean',
            'opening_balance' => 'nullable|numeric',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $coa->update($validated);

        return redirect()->back()->with('success', "Akun COA {$coa->account_code} berhasil diperbarui.");
    }

    /**
     * Remove or deactivate the specified account.
     */
    public function destroy(FinCoaAccount $coa): RedirectResponse
    {
        // Guard 1: Prevent deletion if it has children
        if ($coa->children()->exists()) {
            return redirect()->back()->with('error', "Akun {$coa->account_code} memiliki sub-akun anak. Hapus atau pindahkan sub-akun terlebih dahulu.");
        }

        // Guard 2: Prevent deletion if journal records exist
        $hasGl = FinGlEntry::where('account_code', $coa->account_code)->exists();
        $hasAdj = FinAdjJournalLine::where('account_code', $coa->account_code)->exists();

        if ($hasGl || $hasAdj) {
            $coa->update(['is_active' => false]);

            return redirect()->back()->with('warning', "Akun {$coa->account_code} sudah memiliki riwayat mutasi transaksi buku besar. Akun dinonaktifkan alih-alih dihapus permanen.");
        }

        $code = $coa->account_code;
        $coa->delete();

        return redirect()->back()->with('success', "Akun COA {$code} berhasil dihapus.");
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggleStatus(FinCoaAccount $coa): RedirectResponse
    {
        $coa->update(['is_active' => ! $coa->is_active]);
        $status = $coa->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->back()->with('success', "Akun {$coa->account_code} berhasil {$status}.");
    }

    /**
     * Export COA list as CSV.
     */
    public function export(): StreamedResponse
    {
        $accounts = FinCoaAccount::with(['fsli', 'parent'])
            ->orderBy('account_code')
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Chart_of_Accounts_'.date('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($accounts) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Kode Akun', 'Nama Akun', 'Tipe Akun', 'Saldo Normal', 'Akun Induk', 'Pos FSLI', 'Status', 'Saldo Awal', 'Deskripsi']);

            foreach ($accounts as $a) {
                fputcsv($handle, [
                    $a->account_code,
                    $a->account_name,
                    ucfirst($a->account_type),
                    strtoupper($a->normal_balance),
                    $a->parent?->account_code ? "{$a->parent->account_code} - {$a->parent->account_name}" : '-',
                    $a->fsli?->name ?? '-',
                    $a->is_active ? 'Aktif' : 'Nonaktif',
                    number_format((float) $a->opening_balance, 2, '.', ''),
                    $a->description ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
