<?php

namespace App\Modules\Production\Controllers;

use App\Core\Integrations\Contracts\ProductionIntegrationContract;
use App\Http\Controllers\Controller;
use App\Modules\Production\Models\LaporanHarian;
use App\Modules\Production\Models\ProductionDowntimeReason;
use App\Modules\Production\Models\ProductionLine;
use App\Modules\Production\Models\ProductionMachine;
use App\Modules\Production\Models\WeeklyPlan;
use App\Modules\Production\Services\OeeCalculationService;
use App\Modules\Production\Services\ProductionNumberingService;
use App\Modules\Production\Services\ProductionTimerExecutionService;
use App\Modules\Production\Services\RejectScrapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class DailyReportExecutionController extends Controller
{
    public function __construct(
        protected ProductionTimerExecutionService $timerService,
        protected OeeCalculationService $oeeService,
        protected RejectScrapService $rejectService,
        protected ProductionNumberingService $numberingService
    ) {}

    /**
     * Display list of Daily Production Reports (Laporan Harian Produksi).
     */
    public function index(Request $request): Response
    {
        $query = LaporanHarian::with(['product', 'line', 'operator', 'lockedBy'])->latest('tanggal');

        if ($lineId = $request->input('line_id')) {
            $query->where('line_id', $lineId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($proses = $request->input('proses')) {
            $query->where('proses', $proses);
        }

        if ($shift = $request->input('shift')) {
            $query->where('shift', $shift);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('report_number', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        $reports = $query->paginate(15)->withQueryString();

        return Inertia::render('Modules/Produksi/LaporanHarian/Index', [
            'reports' => $reports,
            'lines' => ProductionLine::where('is_active', true)->get(),
            'filters' => $request->only(['line_id', 'status', 'proses', 'shift', 'search']),
        ]);
    }

    /**
     * Show form for creating a new Daily Report from available Weekly Plan.
     */
    public function create(Request $request): Response
    {
        $weeklyPlans = WeeklyPlan::with(['product', 'line'])
            ->where('status', 'aktif')
            ->latest('tanggal')
            ->get();

        return Inertia::render('Modules/Produksi/LaporanHarian/Form', [
            'weeklyPlans' => $weeklyPlans,
            'lines' => ProductionLine::where('is_active', true)->get(),
            'machines' => ProductionMachine::where('is_active', true)->get(),
            'selectedPlanId' => $request->input('weekly_plan_id'),
        ]);
    }

    /**
     * Store newly created Daily Report.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'weekly_plan_id' => 'required|exists:weekly_plans,id',
            'mesin_id' => 'nullable|exists:production_machines,id',
            'tanggal' => 'required|date',
            'shift' => 'required|in:shift1,shift2,shift3',
            'notes' => 'nullable|string',
        ]);

        $plan = WeeklyPlan::findOrFail($validated['weekly_plan_id']);
        $machine = ! empty($validated['mesin_id']) ? ProductionMachine::find($validated['mesin_id']) : null;
        $ct = $machine ? (float) $machine->cycle_time_seconds : 1.20;

        $reportNumber = $this->numberingService->generateReportNumber();

        $report = LaporanHarian::create([
            'report_number' => $reportNumber,
            'weekly_plan_id' => $plan->id,
            'produk_id' => $plan->produk_id,
            'line_id' => $plan->line_id,
            'mesin_id' => $validated['mesin_id'] ?? null,
            'user_id' => auth()->id() ?? 1,
            'tanggal' => $validated['tanggal'],
            'shift' => $validated['shift'],
            'batch_number' => $plan->batch_number,
            'proses' => $plan->proses,
            'ct' => $ct,
            'timer_status' => 'draft',
            'status' => 'draft',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('production.daily-reports.show', $report->id)
            ->with('success', "Laporan Harian {$report->report_number} siap dieksekusi di konsol timer.");
    }

    /**
     * Display live shop floor execution console & digital timer.
     */
    public function show(int $id): Response
    {
        $report = LaporanHarian::with([
            'weeklyPlan',
            'product',
            'line',
            'machine',
            'operator',
            'lockedBy',
            'downtimeDetails.alasanDowntime',
            'rejectDetails',
        ])->findOrFail($id);

        $downtimeReasons = ProductionDowntimeReason::where('is_active', true)->get();

        return Inertia::render('Modules/Produksi/LaporanHarian/Timer', [
            'report' => $report,
            'downtimeReasons' => $downtimeReasons,
        ]);
    }

    /**
     * Start timer.
     */
    public function start(int $id, Request $request): RedirectResponse
    {
        $this->timerService->startTimer($id, $request->input('start_time'));

        return redirect()->back()->with('success', 'Timer produksi berhasil dijalankan.');
    }

    /**
     * Pause timer.
     */
    public function pause(int $id, Request $request): RedirectResponse
    {
        $this->timerService->pauseTimer($id, $request->input('pause_time'));

        return redirect()->back()->with('warning', 'Timer dijeda. Harap isi alasan downtime saat melanjutkan (Resume).');
    }

    /**
     * Resume timer with mandatory downtime modal.
     */
    public function resume(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'alasan_downtime_id' => 'nullable|exists:production_downtime_reasons,id',
            'kategori' => 'required|in:mesin,bahan,vendor,operasional',
            'alasan_nama' => 'nullable|string|max:255',
            'durasi_menit' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $this->timerService->resumeTimer($id, $validated);

        return redirect()->back()->with('success', 'Produksi dilanjutkan dan jeda waktu henti (downtime) berhasil dicatat.');
    }

    /**
     * End timer.
     */
    public function end(int $id, Request $request): RedirectResponse
    {
        $this->timerService->endTimer($id, $request->input('end_time'));

        return redirect()->back()->with('success', 'Timer dihentikan. Draf estafet proses selanjutnya otomatis disiapkan.');
    }

    /**
     * Submit physical outputs and compute OEE, Yield, and Productivity.
     */
    public function submit(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'capacity_fisik' => 'required|integer|min:0',
            'output_fisik' => 'required|integer|min:0',
            'total_reject' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $report = LaporanHarian::findOrFail($id);
        $totalReject = (int) ($validated['total_reject'] ?? $report->rejectDetails()->sum('jumlah'));

        $this->oeeService->calculateOeeMetrics(
            reportId: $id,
            capacityFisik: (int) $validated['capacity_fisik'],
            outputFisik: (int) $validated['output_fisik'],
            totalReject: $totalReject
        );

        $report->update([
            'status' => 'submitted',
            'notes' => $validated['notes'] ?? $report->notes,
        ]);

        return redirect()->back()->with('success', 'Hasil output fisik dan metrik OEE berhasil dihitung dan diajukan ke Supervisor.');
    }

    /**
     * Supervisor verification & official locking.
     */
    public function lock(int $id, Request $request, ProductionIntegrationContract $productionIntegration): RedirectResponse
    {
        $report = $this->oeeService->lockReport($id, auth()->id() ?? 1);

        // Sync production batch metrics to Finance P&L and HRIS KPIs
        $productionIntegration->syncProductionBatchToFinanceAndHris($report, $request->user());

        return redirect()->back()->with('success', 'Laporan Harian Produksi resmi diverifikasi dan dikunci. Data berhasil disinkronisasi ke Finance & HRIS.');
    }

    /**
     * Create Deviation Report draft in QMS from reject / critical downtime.
     */
    public function spawnDeviation(int $id, Request $request, ProductionIntegrationContract $productionIntegration): RedirectResponse
    {
        $report = LaporanHarian::with(['product', 'machine', 'line'])->findOrFail($id);

        $override = $request->only(['topik', 'jenis_penyimpangan', 'identifikasi_penyimpangan', 'immediate_action_details']);
        $deviation = $productionIntegration->spawnQualityDeviation($report, $override, $request->user());

        return redirect()->back()
            ->with('success', "Draf Deviation Report ({$deviation->deviation_number}) berhasil dibuat di modul Quality (QMS) untuk diinvestigasi.");
    }

    /**
     * Export Daily Report to PDF.
     */
    public function exportPdf(int $id): HttpResponse
    {
        $report = LaporanHarian::with([
            'weeklyPlan',
            'product',
            'line',
            'machine',
            'operator',
            'lockedBy',
            'downtimeDetails',
            'rejectDetails',
        ])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.production_lhp', [
            'report' => $report,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("LHP-{$report->report_number}.pdf");
    }
}
