<?php

namespace App\Modules\Finance\Controllers;

use App\Core\Models\Department;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Finance\Models\FinAdjJournal;
use App\Modules\Finance\Models\FinAdjJournalLine;
use App\Modules\Finance\Models\FinCoaAccount;
use App\Modules\Finance\Models\FinFsli;
use App\Modules\Finance\Models\FinPeriod;
use App\Modules\Finance\Services\FinanceNumberingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AdjJournalController extends Controller
{
    public function __construct(
        protected FinanceNumberingService $numberingService,
        protected ModuleManagerService $moduleManager
    ) {}

    public function index(Request $request): Response
    {
        $query = FinAdjJournal::with(['period', 'creator', 'approver'])
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('period_id')) {
            $query->where('period_id', $request->input('period_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('journal_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $journals = $query->paginate(12)->withQueryString();

        return Inertia::render('Modules/Finance/AdjJournal/Index', [
            'module' => [
                'name' => 'Jurnal Penyesuaian (AJE)',
                'slug' => 'finance',
                'color_theme' => 'emerald',
                'menus' => $this->moduleManager->getModuleMenus('finance'),
            ],
            'journals' => $journals,
            'periods' => FinPeriod::latest('id')->limit(12)->get(),
            'filters' => $request->only(['status', 'period_id', 'search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Modules/Finance/AdjJournal/Form', [
            'module' => [
                'name' => 'Jurnal Penyesuaian (AJE)',
                'slug' => 'finance',
                'color_theme' => 'emerald',
                'menus' => $this->moduleManager->getModuleMenus('finance'),
            ],
            'periods' => FinPeriod::orderByDesc('id')->get(),
            'coaAccounts' => FinCoaAccount::with('fsli')->where('is_active', true)->get(),
            'departments' => Department::select('id', 'name', 'code')->get(),
            'fslis' => FinFsli::all(),
            'categories' => [
                'accrual' => 'Beban Akrual Belum Ditagih (Accrued Expense)',
                'depreciation' => 'Penyusutan Aset Tetap Pabrik (Depreciation)',
                'amortization' => 'Amortisasi Biaya Dibayar Dimuka (Prepaid Expense)',
                'bad_debt' => 'Cadangan Kerugian Piutang (Bad Debt Allowance)',
                'correction' => 'Koreksi Salah Klasifikasi / Posting Error',
                'other' => 'Penyesuaian Akhir Periode Lainnya',
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_id' => 'required|exists:fin_periods,id',
            'journal_date' => 'required|date',
            'category' => 'required|string',
            'description' => 'required|string|max:500',
            'attachment_path' => 'nullable|string',
            'lines' => 'required|array|min:2',
            'lines.*.coa_account_id' => 'nullable|exists:fin_coa_accounts,id',
            'lines.*.account_code' => 'required|string',
            'lines.*.account_name' => 'required|string',
            'lines.*.fsli_id' => 'required|exists:fin_fsli,id',
            'lines.*.department_id' => 'nullable|exists:departments,id',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
            'lines.*.description' => 'nullable|string',
            'as_draft' => 'nullable|boolean',
        ]);

        $period = FinPeriod::findOrFail($validated['period_id']);

        $journal = DB::transaction(function () use ($validated, $period, $request) {
            $totalDebit = collect($validated['lines'])->sum('debit');
            $totalCredit = collect($validated['lines'])->sum('credit');

            $asDraft = (bool) ($request->input('as_draft', false));

            if (! $asDraft && abs($totalDebit - $totalCredit) > 0.01) {
                throw ValidationException::withMessages([
                    'balance' => 'Jurnal penyesuaian harus seimbang! Total Debet (Rp '.number_format($totalDebit, 0).') != Total Kredit (Rp '.number_format($totalCredit, 0).').',
                ]);
            }

            $user = $request->user() ?? User::first();
            $journalNumber = $this->numberingService->generateAjeNumber(Carbon::parse($validated['journal_date']));

            $journal = FinAdjJournal::create([
                'journal_number' => $journalNumber,
                'fiscal_year_id' => $period->fiscal_year_id,
                'period_id' => $period->id,
                'journal_date' => $validated['journal_date'],
                'description' => $validated['description'],
                'category' => $validated['category'],
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'status' => $asDraft ? 'draft' : 'submitted',
                'attachment_path' => $validated['attachment_path'] ?? null,
                'created_by' => $user->id,
            ]);

            foreach ($validated['lines'] as $line) {
                FinAdjJournalLine::create([
                    'adj_journal_id' => $journal->id,
                    'coa_account_id' => $line['coa_account_id'] ?? null,
                    'account_code' => $line['account_code'],
                    'account_name' => $line['account_name'],
                    'fsli_id' => $line['fsli_id'],
                    'department_id' => $line['department_id'] ?? null,
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'description' => $line['description'] ?? null,
                ]);
            }

            return $journal;
        });

        return redirect()->route('finance.aje.show', $journal->id)
            ->with('success', "Jurnal Penyesuaian {$journal->journal_number} berhasil disimpan.");
    }

    public function show(int $id): Response
    {
        $journal = FinAdjJournal::with([
            'period',
            'creator',
            'approver',
            'lines.fsli',
            'lines.department',
        ])->findOrFail($id);

        return Inertia::render('Modules/Finance/AdjJournal/Show', [
            'module' => [
                'name' => 'Jurnal Penyesuaian (AJE)',
                'slug' => 'finance',
                'color_theme' => 'emerald',
                'menus' => $this->moduleManager->getModuleMenus('finance'),
            ],
            'journal' => $journal,
        ]);
    }

    public function submit(int $id): RedirectResponse
    {
        $journal = FinAdjJournal::findOrFail($id);
        if (! $journal->isBalanced()) {
            return back()->withErrors(['balance' => 'Jurnal harus berimbang sebelum diajukan.']);
        }

        $journal->update(['status' => 'submitted']);

        return back()->with('success', "Jurnal {$journal->journal_number} berhasil diajukan untuk verifikasi Maker-Checker.");
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $journal = FinAdjJournal::findOrFail($id);
        $user = $request->user() ?? User::first();

        $journal->update([
            'status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => Carbon::now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', "Jurnal Penyesuaian {$journal->journal_number} telah disetujui dan masuk ke Kertas Kerja Trial Balance.");
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $journal = FinAdjJournal::findOrFail($id);
        $reason = $request->input('rejection_reason') ?? $request->input('reason') ?? 'Ditolak oleh Checker';

        $user = $request->user() ?? User::first();

        $journal->update([
            'status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => Carbon::now(),
            'rejection_reason' => $reason,
        ]);

        return back()->with('success', "Jurnal Penyesuaian {$journal->journal_number} telah ditolak dengan catatan evaluasi.");
    }

    public function reverse(Request $request, int $id): RedirectResponse
    {
        $journal = FinAdjJournal::findOrFail($id);

        $journal->update([
            'status' => 'reversed',
        ]);

        return back()->with('success', "Jurnal Penyesuaian {$journal->journal_number} berhasil dibatalkan (Reversed).");
    }

    public function exportPdf(int $id): HttpResponse
    {
        $journal = FinAdjJournal::with(['period', 'creator', 'approver', 'lines.department'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.finance_aje', [
            'journal' => $journal,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("AJE_{$journal->journal_number}.pdf");
    }
}
