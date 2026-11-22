<?php

namespace App\Modules\Production\Controllers;

use App\Core\Models\Product;
use App\Http\Controllers\Controller;
use App\Modules\Production\Models\ProductionLine;
use App\Modules\Production\Models\WeeklyPlan;
use App\Modules\Production\Services\ProductionNumberingService;
use App\Modules\Production\Services\WpsScheduleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class WpsScheduleController extends Controller
{
    public function __construct(
        protected WpsScheduleService $wpsService,
        protected ProductionNumberingService $numberingService
    ) {}

    /**
     * Display Weekly Production Schedule (WPS) list and Kanban view.
     */
    public function index(Request $request): Response
    {
        $query = WeeklyPlan::with(['product', 'line', 'creator'])->latest('tanggal');

        if ($lineId = $request->input('line_id')) {
            $query->where('line_id', $lineId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($proses = $request->input('proses')) {
            $query->where('proses', $proses);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('plan_number', 'like', "%{$search}%")
                    ->orWhere('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        $plans = $query->paginate(15)->withQueryString();

        $kanban = [
            'draft' => WeeklyPlan::with(['product', 'line'])->where('status', 'draft')->latest()->take(10)->get(),
            'aktif' => WeeklyPlan::with(['product', 'line'])->where('status', 'aktif')->latest()->take(10)->get(),
            'selesai' => WeeklyPlan::with(['product', 'line'])->where('status', 'selesai')->latest()->take(10)->get(),
            'cancelled' => WeeklyPlan::with(['product', 'line'])->where('status', 'cancelled')->latest()->take(10)->get(),
        ];

        return Inertia::render('Modules/Produksi/Wps/Index', [
            'plans' => $plans,
            'kanban' => $kanban,
            'lines' => ProductionLine::where('is_active', true)->get(),
            'products' => Product::where('is_active', true)->get(),
            'filters' => $request->only(['line_id', 'status', 'proses', 'search']),
        ]);
    }

    /**
     * Show form for creating a new WPS.
     */
    public function create(): Response
    {
        return Inertia::render('Modules/Produksi/Wps/Form', [
            'plan' => null,
            'lines' => ProductionLine::where('is_active', true)->get(),
            'products' => Product::where('is_active', true)->get(),
            'defaultBatchNumber' => $this->numberingService->generateBatchNumber(),
        ]);
    }

    /**
     * Store newly created WPS in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'produk_id' => 'required|exists:products,id',
            'line_id' => 'required|exists:production_lines,id',
            'proses' => 'required|in:mixing,filling,packing',
            'batch_number' => 'required|string|max:100',
            'odoo_mo_id' => 'nullable|integer',
            'mo_status' => 'nullable|in:confirmed,done,cancelled,manual',
            'target_output' => 'nullable|integer|min:1',
            'mp_count' => 'required|integer|min:1',
            'multiplier' => 'nullable|integer|min:1',
            'packing_hold' => 'nullable|boolean',
            'tanggal' => 'required|date',
            'shift' => 'required|in:shift1,shift2,shift3',
            'notes' => 'nullable|string',
        ]);

        try {
            $this->wpsService->createWeeklyPlan($validated, auth()->id());

            return redirect()->route('production.schedules.index')
                ->with('success', "Rencana Produksi Mingguan (WPS) Batch {$validated['batch_number']} berhasil dijadwalkan.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat jadwal: '.$e->getMessage());
        }
    }

    /**
     * Toggle Packing Hold status.
     */
    public function toggleHold(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'is_hold' => 'required|boolean',
            'reason' => 'nullable|string',
        ]);

        $this->wpsService->togglePackingHold($id, (bool) $validated['is_hold'], $validated['reason'] ?? null);

        $statusStr = $validated['is_hold'] ? 'di-HOLD' : 'di-RESUME';

        return redirect()->back()->with('success', "Status Packing Hold berhasil diperbarui ({$statusStr}).");
    }

    /**
     * Sync Odoo Manufacturing Order.
     */
    public function syncOdoo(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'odoo_mo_id' => 'required|integer',
            'mo_status' => 'required|string',
        ]);

        $this->wpsService->syncOdooMo((int) $validated['odoo_mo_id'], $validated['mo_status']);

        return redirect()->back()->with('success', 'Status MO Odoo berhasil disinkronkan ke jadwal produksi.');
    }

    /**
     * Export WPS / SPK to PDF.
     */
    public function exportPdf(int $id): HttpResponse
    {
        $plan = WeeklyPlan::with(['product', 'line', 'creator', 'laporanHarians'])->findOrFail($id);

        $pdf = Pdf::loadView('pdf.production_spk', [
            'plan' => $plan,
        ])->setPaper('a4', 'portrait');

        return $pdf->download("SPK-WPS-{$plan->plan_number}.pdf");
    }
}
