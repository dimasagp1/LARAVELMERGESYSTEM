<?php

namespace App\Modules\SupplyChain\Services;

use App\Core\Models\Bom;
use App\Core\Models\Product;
use App\Models\User;
use App\Modules\SupplyChain\Models\ScmInventoryStock;
use App\Modules\SupplyChain\Models\ScmMrpItem;
use App\Modules\SupplyChain\Models\ScmMrpRun;
use App\Modules\SupplyChain\Models\ScmProductionSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MrpCalculatorService
{
    public function __construct(
        protected ScmNumberingService $numberingService
    ) {}

    /**
     * Run full MRP calculation from a production schedule or active period.
     */
    public function runMrp(?int $scheduleId = null, ?int $userId = null, ?int $plantId = null, int $horizonMonths = 3, ?string $notes = null): ScmMrpRun
    {
        return DB::transaction(function () use ($scheduleId, $userId, $plantId, $horizonMonths, $notes) {
            $scheduleQuery = ScmProductionSchedule::whereIn('status', ['confirmed', 'in_progress', 'draft']);
            if ($plantId) {
                $scheduleQuery->where('plant_id', $plantId);
            }
            $schedule = $scheduleId ? ScmProductionSchedule::find($scheduleId) : $scheduleQuery->first();
            $user = $userId ? User::find($userId) : (auth()->user() ?? User::first());

            $periodName = Carbon::now()->translatedFormat('F Y');

            $mrpRun = ScmMrpRun::create([
                'run_number' => $this->numberingService->generateMrpRun(),
                'schedule_id' => $schedule?->id,
                'period_name' => $periodName,
                'parameters_json' => [
                    'schedule_number' => $schedule?->schedule_number ?? 'ALL-ACTIVE',
                    'lead_time_days_default' => 14,
                    'include_quarantine' => false,
                    'plant_id' => $plantId,
                    'horizon_months' => $horizonMonths,
                ],
                'status' => 'simulated',
                'executed_by' => $user?->id ?? 1,
                'notes' => $notes ?? 'Simulasi MRP otomatis berbasis Jadwal Induk Produksi (MPS) & Resep BOM.',
            ]);

            // Determine target products to explode
            $schedules = $schedule
                ? collect([$schedule])
                : ScmProductionSchedule::with(['product', 'bom.items'])->whereIn('status', ['confirmed', 'draft'])->get();

            if ($schedules->isEmpty()) {
                // If no active schedules, analyze all raw materials against safety stock
                $this->generateSafetyStockMrpItems($mrpRun);

                return $mrpRun;
            }

            foreach ($schedules as $mps) {
                $targetQty = (float) $mps->planned_quantity;
                $bom = $mps->bom ?? Bom::with('items')->where('product_id', $mps->product_id)->where('is_active', true)->first();

                if (! $bom) {
                    continue;
                }

                foreach ($bom->items as $bomItem) {
                    $rawProduct = Product::find($bomItem->product_id);
                    if (! $rawProduct) {
                        continue;
                    }

                    $scrapMultiplier = 1 + (($bomItem->scrap_percentage ?? 0) / 100);
                    $grossReq = $targetQty * (float) $bomItem->quantity * $scrapMultiplier;

                    // Current stock status across all warehouses
                    $onHand = (float) ScmInventoryStock::where('product_id', $rawProduct->id)
                        ->where('status', 'available')
                        ->sum('quantity_available');

                    $allocated = (float) ScmInventoryStock::where('product_id', $rawProduct->id)
                        ->sum('quantity_reserved');

                    // Outstanding PO simulation
                    $outstandingPo = 0.0;
                    if (DB::getSchemaBuilder()->hasTable('pr_items')) {
                        $outstandingPo = (float) DB::table('pr_items')
                            ->where('product_id', $rawProduct->id)
                            ->whereIn('status', ['ordered', 'ready_to_po'])
                            ->sum('quantity');
                    }

                    $safetyStock = (float) ($rawProduct->min_stock ?? 50.0);

                    // Formula: Net = (Gross) - (Available) - (PO) + (Safety)
                    $netReq = ($grossReq - $onHand - $outstandingPo + $safetyStock);
                    $netReq = max(0, round($netReq, 2));

                    $leadTimeDays = (int) ($rawProduct->lead_time_days ?? 7);
                    $dueDate = Carbon::parse($mps->start_date);
                    $suggestedOrderDate = $dueDate->copy()->subDays($leadTimeDays);

                    ScmMrpItem::create([
                        'mrp_run_id' => $mrpRun->id,
                        'product_id' => $rawProduct->id,
                        'bom_id' => $bom->id,
                        'gross_requirement' => round($grossReq, 2),
                        'on_hand_stock' => $onHand,
                        'allocated_stock' => $allocated,
                        'outstanding_po_qty' => $outstandingPo,
                        'safety_stock' => $safetyStock,
                        'net_requirement' => $netReq,
                        'suggested_order_date' => $suggestedOrderDate->isPast() ? Carbon::now()->toDateString() : $suggestedOrderDate->toDateString(),
                        'suggested_due_date' => $dueDate->toDateString(),
                        'status' => $netReq > 0 ? 'pending' : 'satisfied',
                    ]);
                }
            }

            return $mrpRun;
        });
    }

    /**
     * Fallback MRP calculation checking raw materials below safety stock.
     */
    protected function generateSafetyStockMrpItems(ScmMrpRun $mrpRun): void
    {
        $rawMaterials = Product::where('type', 'raw_material')->orWhere('type', 'packaging')->get();

        foreach ($rawMaterials as $mat) {
            $onHand = (float) ScmInventoryStock::where('product_id', $mat->id)
                ->where('status', 'available')
                ->sum('quantity_available');

            $safety = (float) ($mat->min_stock ?? 50.0);
            $net = max(0, $safety - $onHand);

            ScmMrpItem::create([
                'mrp_run_id' => $mrpRun->id,
                'product_id' => $mat->id,
                'bom_id' => null,
                'gross_requirement' => $safety,
                'on_hand_stock' => $onHand,
                'allocated_stock' => 0,
                'outstanding_po_qty' => 0,
                'safety_stock' => $safety,
                'net_requirement' => $net,
                'suggested_order_date' => Carbon::now()->toDateString(),
                'suggested_due_date' => Carbon::now()->addDays(7)->toDateString(),
                'status' => $net > 0 ? 'pending' : 'satisfied',
            ]);
        }
    }

    /**
     * Get Smart SCM Alerts: Under-Stock, Near-Expiry, Late Inbound.
     */
    public function getSmartAlerts(): array
    {
        // 1. Under-stock alerts
        $underStockAlerts = [];
        $rawMaterials = Product::whereIn('type', ['raw_material', 'packaging'])->get();

        foreach ($rawMaterials as $item) {
            $available = (float) ScmInventoryStock::where('product_id', $item->id)
                ->where('status', 'available')
                ->sum('quantity_available');

            $min = (float) ($item->min_stock ?? 50.0);
            if ($available < $min) {
                $underStockAlerts[] = [
                    'product_id' => $item->id,
                    'code' => $item->code,
                    'name' => $item->name,
                    'available' => $available,
                    'min_stock' => $min,
                    'deficit' => round($min - $available, 2),
                    'severity' => $available == 0 ? 'critical' : 'warning',
                ];
            }
        }

        // 2. Near expiry alerts (<90 days)
        $nearExpiryItems = ScmInventoryStock::with(['product', 'location'])
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', Carbon::now()->addDays(90))
            ->where('quantity_on_hand', '>', 0)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'product_name' => $s->product?->name ?? 'Material',
                'batch_number' => $s->batch_number,
                'location' => $s->location?->name,
                'quantity' => (float) $s->quantity_on_hand,
                'expiry_date' => $s->expiry_date?->format('d M Y'),
                'days_remaining' => Carbon::now()->diffInDays($s->expiry_date, false),
            ]);

        return [
            'under_stock' => $underStockAlerts,
            'under_stock_count' => count($underStockAlerts),
            'near_expiry' => $nearExpiryItems,
            'near_expiry_count' => $nearExpiryItems->count(),
        ];
    }
}
