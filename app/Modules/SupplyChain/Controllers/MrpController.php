<?php

namespace App\Modules\SupplyChain\Controllers;

use App\Core\Integrations\Contracts\ProcurementIntegrationContract;
use App\Core\Models\Plant;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\SupplyChain\Models\ScmMrpRun;
use App\Modules\SupplyChain\Models\ScmProductionSchedule;
use App\Modules\SupplyChain\Services\MrpCalculatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MrpController extends Controller
{
    public function __construct(
        protected MrpCalculatorService $mrpService
    ) {}

    /**
     * Display the MRP Simulation & Net Requirements dashboard.
     */
    public function index(): Response
    {
        $mrpRuns = ScmMrpRun::with(['planner', 'schedule.plant'])
            ->withCount('items')
            ->latest()
            ->paginate(10);

        $latestRun = ScmMrpRun::with(['items.product.uom', 'items.vendor', 'schedule.plant', 'planner'])
            ->latest()
            ->first();

        $activeSchedules = ScmProductionSchedule::with(['product.uom', 'plant'])
            ->whereIn('status', ['confirmed', 'in_progress'])
            ->latest('start_date')
            ->take(10)
            ->get();

        $plants = Plant::where('is_active', true)->get(['id', 'code', 'name']);

        return Inertia::render('Modules/SupplyChain/Mrp/Index', [
            'mrpRuns' => $mrpRuns,
            'latestRun' => $latestRun,
            'activeSchedules' => $activeSchedules,
            'plants' => $plants,
        ]);
    }

    /**
     * Trigger a new MRP calculation run for a specific plant and planning horizon.
     */
    public function runMrp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plant_id' => 'required|exists:plants,id',
            'planning_horizon_months' => 'nullable|integer|min:1|max:12',
            'notes' => 'nullable|string|max:500',
        ]);

        $mrpRun = $this->mrpService->runMrp(
            scheduleId: null,
            userId: auth()->id(),
            plantId: (int) $validated['plant_id'],
            horizonMonths: (int) ($validated['planning_horizon_months'] ?? 3),
            notes: $validated['notes'] ?? 'Simulasi Ledakan Kebutuhan Material SCM'
        );

        return redirect()->route('supply-chain.mrp.index')
            ->with('success', "Kalkulasi MRP berhasil dieksekusi ({$mrpRun->run_number}) dengan {$mrpRun->items()->count()} item kebutuhan bahan.");
    }

    /**
     * View detailed explosion items of a specific MRP run.
     */
    public function show(int $id): Response
    {
        $run = ScmMrpRun::with(['items.product.uom', 'items.vendor', 'schedule.plant', 'planner'])
            ->findOrFail($id);

        return Inertia::render('Modules/SupplyChain/Mrp/Show', [
            'mrpRun' => $run,
        ]);
    }

    /**
     * Convert MRP net requirements into a Draft PR in Procurement.
     */
    public function convertToPr(Request $request, int $id, ProcurementIntegrationContract $procurementIntegration): RedirectResponse
    {
        $mrpRun = ScmMrpRun::with(['items.product.uom', 'schedule.plant'])->findOrFail($id);
        $user = $request->user() ?? User::first();

        $pendingItems = $mrpRun->items()->where('net_requirement', '>', 0)->get();

        if ($pendingItems->isEmpty()) {
            return back()->with('error', 'Tidak ada rekomendasi kebutuhan bersih (Net Requirement > 0) pada kalkulasi MRP ini.');
        }

        $itemsData = [];
        foreach ($pendingItems as $item) {
            $itemsData[] = [
                'mrp_item_id' => $item->id,
                'product_id' => $item->product_id,
                'item_name' => $item->product?->name ?? 'Material Item',
                'quantity' => (float) $item->net_requirement,
                'uom' => $item->product?->uom?->code ?? 'Kg',
                'estimated_price' => (float) ($item->product?->standard_cost ?? 0),
            ];
        }

        $pr = $procurementIntegration->createDraftPrFromMrp([
            'mrp_run_id' => $mrpRun->id,
            'department_id' => 1,
            'purpose' => "Pengadaan Material SCM MRP ({$mrpRun->run_number})",
        ], $itemsData, $user);

        return redirect()->route('procurement.purchase-requests.index')
            ->with('success', "Rekomendasi kebutuhan bersih MRP berhasil dikonversi menjadi Draft PR ({$pr->pr_number}) di modul Procurement.");
    }
}
