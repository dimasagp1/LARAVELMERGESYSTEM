<?php

namespace App\Core\Integrations\Services;

use App\Core\Integrations\Contracts\FinanceBudgetIntegrationContract;
use App\Core\Integrations\Contracts\HrisIntegrationContract;
use App\Core\Integrations\Contracts\ProcurementIntegrationContract;
use App\Core\Integrations\Contracts\ProductionIntegrationContract;
use App\Core\Integrations\Contracts\RndIntegrationContract;
use App\Core\Integrations\Contracts\SupplyChainIntegrationContract;
use App\Core\Integrations\Events\FinanceMetricsPublishedEvent;
use App\Core\Integrations\Events\FormulaPromotedToBomEvent;
use App\Core\Integrations\Events\HrisHeadcountSyncedEvent;
use App\Core\Integrations\Events\MrpConvertedToPrEvent;
use App\Core\Integrations\Events\ProductionDeviationReportCreatedEvent;
use App\Core\Integrations\Events\PurchaseOrderApprovedEvent;
use App\Core\Integrations\Events\QcInspectionCompletedEvent;
use App\Core\Models\Bom;
use App\Core\Models\BomItem;
use App\Core\Models\Location;
use App\Core\Models\Plant;
use App\Core\Models\Product;
use App\Core\Models\Uom;
use App\Core\Models\Vendor;
use App\Models\User;
use App\Modules\Finance\Models\FinHeadcount;
use App\Modules\Finance\Models\FinMonthlyBudget;
use App\Modules\Finance\Models\FinPeriod;
use App\Modules\Finance\Services\BudgetGatekeeperService;
use App\Modules\Finance\Services\FinancialRatioService;
use App\Modules\Hris\Models\HrisAttendance;
use App\Modules\Hris\Models\HrisDepartmentObjective;
use App\Modules\Hris\Models\HrisEmployee;
use App\Modules\Hris\Models\HrisOvertime;
use App\Modules\Hris\Models\HrisSalary;
use App\Modules\Hris\Services\StrategicBscService;
use App\Modules\Procurement\Models\ItemStandardPrice;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\Procurement\Models\PurchaseRequest;
use App\Modules\Procurement\Services\ProcurementNumberingService;
use App\Modules\Production\Models\LaporanHarian;
use App\Modules\Quality\Models\Deviation;
use App\Modules\Quality\Services\DeviationInvestigationService;
use App\Modules\Rnd\Models\Formula;
use App\Modules\Rnd\Models\RndPriceStaging;
use App\Modules\Rnd\Services\FormulaCalculationService;
use App\Modules\SupplyChain\Models\ScmGoodsReceipt;
use App\Modules\SupplyChain\Models\ScmMrpItem;
use App\Modules\SupplyChain\Models\ScmMrpRun;
use App\Modules\SupplyChain\Models\ScmQcInspection;
use App\Modules\SupplyChain\Services\InboundGoodsReceiptService;
use App\Modules\SupplyChain\Services\QcIncomingGatekeeperService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CrossModuleIntegrationService implements FinanceBudgetIntegrationContract, HrisIntegrationContract, ProcurementIntegrationContract, ProductionIntegrationContract, RndIntegrationContract, SupplyChainIntegrationContract
{
    public function __construct(
        protected NotificationCenterService $notificationService,
        protected BudgetGatekeeperService $budgetGatekeeper,
        protected FormulaCalculationService $formulaCalcService,
        protected ProcurementNumberingService $procurementNumbering,
        protected InboundGoodsReceiptService $goodsReceiptService,
        protected QcIncomingGatekeeperService $qcGatekeeperService,
        protected DeviationInvestigationService $deviationService,
        protected FinancialRatioService $financialRatioService,
        protected StrategicBscService $strategicBscService
    ) {}

    // =========================================================================
    // 1. R&D <-> SCM & Procurement
    // =========================================================================

    /**
     * Promote an approved R&D Formula into Master BOM in Core Kernel.
     */
    public function promoteFormulaToMasterBom(int $formulaId, ?User $user = null): Bom
    {
        return DB::transaction(function () use ($formulaId, $user) {
            $user = $user ?? (auth()->user() ?? User::first());
            $formula = Formula::with(['items.material', 'product.uom'])->findOrFail($formulaId);

            $product = $formula->product;
            if (! $product) {
                throw ValidationException::withMessages([
                    'product_id' => 'Formula belum terhubung dengan Master Produk.',
                ]);
            }

            // Deactivate existing active BOM for this product
            Bom::where('product_id', $product->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $targetServingG = (float) ($formula->target_serving_size_g ?: 100.0);
            $servings = (int) ($formula->servings_per_pack ?: 1);
            $batchSize = round($targetServingG * $servings, 4);

            $bomCode = 'BOM-'.strtoupper($product->sku ?? $product->code ?? 'PRD').'-V'.$formula->version;

            $bom = Bom::create([
                'bom_number' => $bomCode,
                'product_id' => $product->id,
                'version' => 'V'.$formula->version,
                'batch_size' => $batchSize,
                'batch_uom_id' => $product->uom_id ?? 1,
                'status' => 'active',
                'is_active' => true,
                'created_by' => $user?->id ?? $formula->created_by,
                'approved_by' => $user?->id ?? $formula->approved_by_gm,
                'approved_at' => now(),
                'notes' => "Dipromosikan dari Formula R&D: {$formula->code} (Versi {$formula->version})",
            ]);

            foreach ($formula->items as $idx => $item) {
                BomItem::create([
                    'bom_id' => $bom->id,
                    'product_id' => $item->material_product_id,
                    'quantity' => (float) $item->weight_per_serving_g * $servings,
                    'percentage' => (float) $item->percentage,
                    'uom_id' => $item->material?->uom_id ?? 1,
                    'wastage_percentage' => 0.0,
                    'stage' => 'Mixing',
                    'instructions' => $item->notes ?? 'Pencampuran bahan formulasi R&D',
                    'sort_order' => $idx + 1,
                ]);
            }

            // Lock formula and flag as promoted
            $formula->update([
                'locked_at' => $formula->locked_at ?? now(),
                'custom_attributes' => array_merge($formula->custom_attributes ?? [], [
                    'promoted_to_bom_id' => $bom->id,
                    'promoted_bom_number' => $bom->bom_number,
                    'promoted_at' => now()->toIso8601String(),
                ]),
            ]);

            event(new FormulaPromotedToBomEvent($formula, $bom, $user));

            $this->notificationService->broadcast(
                moduleSlug: 'supply-chain',
                type: 'master_bom_promoted',
                title: "Master BOM Baru Diterbitkan ({$bom->bom_number})",
                message: "Formula R&D untuk {$product->name} berhasil dipromosikan ke Master BOM dan siap digunakan di SCM MRP & Produksi.",
                actionUrl: '/supply-chain/mrp',
                urgency: 'high'
            );

            return $bom->load('items.product');
        });
    }

    /**
     * Record a new raw material price from Procurement PO into R&D price staging.
     */
    public function stageRawMaterialPrice(
        int $productId,
        float $newPrice,
        ?string $poNumber = null,
        ?int $vendorId = null,
        ?User $user = null
    ): RndPriceStaging {
        return DB::transaction(function () use ($productId, $newPrice, $poNumber, $vendorId, $user) {
            $user = $user ?? (auth()->user() ?? User::first());
            $product = Product::findOrFail($productId);

            $oldPrice = (float) ($product->standard_cost ?? 0);
            $priceDiff = $newPrice - $oldPrice;
            $pctChange = $oldPrice > 0 ? round(($priceDiff / $oldPrice) * 100, 2) : 0.0;

            $staging = RndPriceStaging::create([
                'product_id' => $productId,
                'vendor_id' => $vendorId,
                'source_type' => 'po',
                'po_number' => $poNumber,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'price_difference' => $priceDiff,
                'percentage_change' => $pctChange,
                'status' => 'staged',
                'effective_date' => now()->toDateString(),
                'created_by' => $user?->id,
                'notes' => "Update harga otomatis dari PO {$poNumber}",
            ]);

            // Update item standard price in Procurement catalog & core standard_cost
            if (class_exists(ItemStandardPrice::class)) {
                ItemStandardPrice::updateOrCreate(
                    ['product_id' => $productId],
                    [
                        'item_code' => $product->sku ?? $product->code ?? 'RM-'.$productId,
                        'item_name' => $product->name,
                        'uom' => $product->uom?->code ?? 'Kg',
                        'standard_price' => $newPrice,
                        'last_po_price' => $newPrice,
                        'vendor_id' => $vendorId,
                        'updated_by' => $user?->id,
                    ]
                );
            }

            $product->update(['standard_cost' => $newPrice]);

            $this->notificationService->broadcast(
                moduleSlug: 'rnd',
                type: 'price_staging_updated',
                title: "Pembaruan Harga Bahan Baku: {$product->name}",
                message: "PO {$poNumber} memperbarui harga dari Rp ".number_format($oldPrice, 0, ',', '.').' menjadi Rp '.number_format($newPrice, 0, ',', '.')." ({$pctChange}%). Data siap untuk simulasi HPP formulasi.",
                actionUrl: '/rnd/formulas',
                urgency: abs($pctChange) > 10 ? 'high' : 'normal'
            );

            return $staging;
        });
    }

    /**
     * Recalculate HPP for an R&D formula using current staged prices.
     */
    public function recalculateFormulaHpp(Formula $formula): array
    {
        $items = $formula->items()->with('material')->get();
        $payloadItems = [];

        foreach ($items as $item) {
            $latestPrice = (float) ($item->material?->standard_cost ?? $item->price_per_kg);
            $payloadItems[] = [
                'material_product_id' => $item->material_product_id,
                'vendor_id' => $item->vendor_id,
                'percentage' => (float) $item->percentage,
                'price_per_kg' => $latestPrice,
                'notes' => $item->notes,
            ];
        }

        $calc = $this->formulaCalcService->calculate(
            $payloadItems,
            (float) ($formula->target_serving_size_g ?: 100),
            (int) ($formula->servings_per_pack ?: 1)
        );

        $formula->update([
            'total_cost_per_kg' => $calc['total_cost_per_kg'],
            'total_cost_per_pack' => $calc['total_cost_per_pack'],
        ]);

        return $calc;
    }

    // =========================================================================
    // 2. SCM (MRP) -> Procurement -> Finance (Budget Gatekeeper)
    // =========================================================================

    /**
     * Convert MRP net requirement recommendations into a Draft PR in Procurement.
     */
    public function createDraftPrFromMrp(array $mrpData, array $itemsData, ?User $user = null): PurchaseRequest
    {
        return DB::transaction(function () use ($mrpData, $itemsData, $user) {
            $user = $user ?? (auth()->user() ?? User::first());
            $departmentId = $mrpData['department_id'] ?? 1; // SCM / Production
            $mrpRunId = $mrpData['mrp_run_id'] ?? null;
            $mrpRun = $mrpRunId ? ScmMrpRun::find($mrpRunId) : null;

            $requestDate = Carbon::now();
            $prNumber = $this->procurementNumbering->generatePrNumber((int) $departmentId, $requestDate);

            $pr = PurchaseRequest::create([
                'pr_number' => $prNumber,
                'user_id' => $user?->id ?? 1,
                'department_id' => $departmentId,
                'request_date' => $requestDate,
                'purpose' => $mrpData['purpose'] ?? 'Pengadaan Bahan Baku Berbasis Rekomendasi MRP',
                'pr_type' => 'operational',
                'status' => 'draft',
                'total_amount' => 0,
                'bypass_budget_check' => false,
                'notes' => 'Dikonversi otomatis dari simulasi SCM MRP'.($mrpRun ? " ({$mrpRun->run_number})" : ''),
                'custom_attributes' => [
                    'source' => 'SCM_MRP',
                    'mrp_run_id' => $mrpRunId,
                ],
            ]);

            $totalEst = 0.0;
            $mrpItemIds = [];

            foreach ($itemsData as $item) {
                $qty = (float) ($item['quantity'] ?? 1);
                $product = isset($item['product_id']) ? Product::find($item['product_id']) : null;
                $estPrice = (float) ($item['estimated_price'] ?? ($product?->standard_cost ?? 0));
                $lineTotal = $estPrice * $qty;
                $totalEst += $lineTotal;

                if (! empty($item['mrp_item_id'])) {
                    $mrpItemIds[] = (int) $item['mrp_item_id'];
                    ScmMrpItem::where('id', $item['mrp_item_id'])->update([
                        'status' => 'converted_to_pr',
                    ]);
                }

                PrItem::create([
                    'purchase_request_id' => $pr->id,
                    'product_id' => $item['product_id'] ?? null,
                    'item_name' => $item['item_name'] ?? ($product?->name ?? 'Material MRP'),
                    'item_code' => $product?->sku ?? $product?->code ?? null,
                    'quantity' => $qty,
                    'ready_quantity' => 0,
                    'uom' => $item['uom'] ?? ($product?->uom?->code ?? 'Kg'),
                    'estimated_price' => $lineTotal,
                    'status' => 'draft',
                    'rekap_po_odoo' => true,
                    'is_incoming' => true,
                    'custom_attributes' => [
                        'mrp_item_id' => $item['mrp_item_id'] ?? null,
                    ],
                ]);
            }

            $pr->update(['total_amount' => $totalEst]);

            if ($mrpRun) {
                event(new MrpConvertedToPrEvent($mrpRun, $pr, $mrpItemIds, $user));
            }

            $this->notificationService->send(
                moduleSlug: 'procurement',
                type: 'pr_draft_created_from_mrp',
                title: "Draft PR Dibuat dari SCM MRP ({$pr->pr_number})",
                message: 'Terdapat draf pengadaan baru hasil perhitungan MRP senilai Rp '.number_format($totalEst, 0, ',', '.').' menunggu pengajuan.',
                actionUrl: '/procurement/purchase-requests',
                urgency: 'normal'
            );

            return $pr->load('items');
        });
    }

    /**
     * Handle an approved PO / ordered PR item event.
     */
    public function handlePoApproved(PrItem $item, User $user): void
    {
        event(new PurchaseOrderApprovedEvent($item, $user));

        // 1. Stage raw material price to R&D
        if ($item->product_id && $item->actual_price > 0 && $item->quantity > 0) {
            $unitPrice = (float) $item->actual_price / (float) $item->quantity;
            $this->stageRawMaterialPrice(
                productId: $item->product_id,
                newPrice: $unitPrice,
                poNumber: $item->po_number,
                vendorId: $item->vendor_id,
                user: $user
            );
        }

        // 2. Open expected Goods Receipt in SCM
        $this->openGoodsReceiptFromPo($item, $user);
    }

    /**
     * Check budget ceiling for a department before committing expense.
     */
    public function checkBudget(int $departmentId, float $requestedAmount, ?int $periodId = null): array
    {
        return $this->budgetGatekeeper->checkBudget($departmentId, $requestedAmount, $periodId);
    }

    /**
     * Commit (book) budget when PR or expense is submitted/approved.
     */
    public function commitBudget(int $departmentId, float $amount, ?int $periodId = null): array
    {
        $this->budgetGatekeeper->bookBudget($departmentId, $amount, $periodId);
        $check = $this->checkBudget($departmentId, 0, $periodId);

        return [
            'success' => true,
            'booked_amount' => $amount,
            'remaining_budget' => $check['remaining_budget'],
        ];
    }

    /**
     * Release (unbook) committed budget when PR is rejected or cancelled.
     */
    public function releaseBudget(int $departmentId, float $amount, ?int $periodId = null): void
    {
        $period = $periodId ? FinPeriod::find($periodId) : FinPeriod::latest('id')->first();
        if (! $period) {
            return;
        }

        $budget = FinMonthlyBudget::where('period_id', $period->id)
            ->where('department_id', $departmentId)
            ->first();

        if ($budget) {
            $newBooked = max(0.0, (float) $budget->booked_amount - $amount);
            $budget->update(['booked_amount' => $newBooked]);
        }
    }

    /**
     * Realize budget when PO goods receipt is verified into actual Accounts Payable.
     */
    public function realizeBudget(int $departmentId, float $amount, ?int $periodId = null): void
    {
        $period = $periodId ? FinPeriod::find($periodId) : FinPeriod::latest('id')->first();
        if (! $period) {
            return;
        }

        $budget = FinMonthlyBudget::where('period_id', $period->id)
            ->where('department_id', $departmentId)
            ->first();

        if ($budget) {
            $newBooked = max(0.0, (float) $budget->booked_amount - $amount);
            $newRealized = (float) $budget->realized_amount + $amount;
            $budget->update([
                'booked_amount' => $newBooked,
                'realized_amount' => $newRealized,
            ]);
        }
    }

    // =========================================================================
    // 3. Procurement (PO) -> SCM (GR & QC) -> Finance (3-Way Matching)
    // =========================================================================

    /**
     * Open or prepare expected Goods Receipt in SCM from an approved Procurement PO item.
     */
    public function openGoodsReceiptFromPo(PrItem $poItem, User $user, ?int $plantId = null): ScmGoodsReceipt
    {
        return DB::transaction(function () use ($poItem, $user, $plantId) {
            $plant = $plantId ? Plant::find($plantId) : Plant::where('is_active', true)->first();
            if (! $plant) {
                $plant = Plant::first() ?? Plant::create([
                    'code' => 'PLANT-01',
                    'name' => 'Pabrik Utama',
                    'is_active' => true,
                ]);
            }
            $plantId = $plant->id;

            // Ensure quarantine location exists for this plant
            $quarantineLoc = Location::where('plant_id', $plantId)->first();
            if (! $quarantineLoc) {
                Location::create([
                    'plant_id' => $plantId,
                    'code' => 'WH-QRN',
                    'name' => 'Gudang Karantina',
                    'is_quarantine' => true,
                ]);
            }

            $vendor = $poItem->vendor_id ? Vendor::find($poItem->vendor_id) : Vendor::first();
            if (! $vendor) {
                $vendor = Vendor::create([
                    'code' => 'VEND-001',
                    'name' => 'Vendor Mitra Utama',
                    'is_active' => true,
                ]);
            }
            $vendorId = $vendor->id;

            $product = $poItem->product_id ? Product::find($poItem->product_id) : Product::first();
            if (! $product) {
                $uom = Uom::first() ?? Uom::create(['code' => 'Kg', 'name' => 'Kilogram']);
                $product = Product::create([
                    'sku' => 'PRD-AUTO-'.strtoupper(substr(uniqid(), -4)),
                    'name' => $poItem->item_name ?: 'Bahan Baku Otomatis',
                    'uom_id' => $uom->id,
                    'is_active' => true,
                ]);
            }
            $productId = $product->id;

            $poNumber = $poItem->po_number ?: 'PO-'.date('Ymd').'-'.str_pad((string) $poItem->id, 4, '0', STR_PAD_LEFT);

            // Check if active Goods Receipt exists for this PO
            $receipt = ScmGoodsReceipt::where('po_number', $poNumber)
                ->whereIn('status', ['quarantine', 'pending'])
                ->first();

            if (! $receipt) {
                $receipt = $this->goodsReceiptService->createGoodsReceipt(
                    data: [
                        'po_number' => $poNumber,
                        'vendor_id' => $vendorId,
                        'plant_id' => $plantId,
                        'delivery_order_number' => 'SJ-'.strtoupper(substr(uniqid(), -6)),
                        'receipt_date' => now()->toDateString(),
                        'notes' => 'Penerimaan masuk dari PO '.$poNumber,
                    ],
                    items: [
                        [
                            'product_id' => $productId,
                            'item_name' => $poItem->item_name ?: $product->name,
                            'received_qty' => (float) max(1, $poItem->quantity),
                            'uom' => $poItem->uom ?: 'Kg',
                            'unit_price' => (float) ($poItem->actual_price > 0 && $poItem->quantity > 0 ? ($poItem->actual_price / $poItem->quantity) : ($poItem->estimated_price > 0 ? $poItem->estimated_price : 10000)),
                        ],
                    ],
                    userId: $user->id
                );

                $this->notificationService->send(
                    moduleSlug: 'supply-chain',
                    type: 'expected_gr_opened',
                    title: "Penerimaan Barang Siap (PO {$poNumber})",
                    message: "PO {$poNumber} siap diterima di Gudang Karantina untuk inspeksi QC Incoming.",
                    actionUrl: '/supply-chain/goods-receipt',
                    urgency: 'normal'
                );
            }

            return $receipt;
        });
    }

    /**
     * Unlock 3-Way Matching AP and transfer passed quantity to active stock upon QC pass.
     */
    public function handleQcInspectionResult(ScmQcInspection $inspection, User $user): array
    {
        $item = $inspection->goodsReceiptItem;
        $receipt = $item->goodsReceipt;
        $isPassed = $inspection->status === 'passed' || $inspection->passed_qty > 0;

        event(new QcInspectionCompletedEvent(
            inspection: $inspection,
            user: $user,
            isPassed: $isPassed,
            passedQty: (float) $inspection->passed_qty,
            rejectedQty: (float) $inspection->rejected_qty
        ));

        if ($isPassed) {
            $this->notificationService->broadcast(
                moduleSlug: 'finance',
                type: 'qc_passed_ready_for_matching',
                title: "Kelulusan QC Bahan ({$item->item_name})",
                message: "Bahan dari PO {$receipt->po_number} lulus QC ({$inspection->passed_qty} {$item->uom}). Izin pembuatan Tagihan Vendor & 3-Way Matching AP terbuka.",
                actionUrl: '/supply-chain/matching',
                urgency: 'normal'
            );
        }

        return [
            'inspection_id' => $inspection->id,
            'status' => $inspection->status,
            'passed_qty' => (float) $inspection->passed_qty,
            'rejected_qty' => (float) $inspection->rejected_qty,
            'ready_for_matching' => $isPassed,
        ];
    }

    // =========================================================================
    // 4. Produksi -> QMS, Finance & HRIS
    // =========================================================================

    /**
     * Spawn a QMS Deviation Report draft from a critical reject or downtime in Laporan Harian Produksi.
     */
    public function spawnQualityDeviation(LaporanHarian $laporanHarian, array $overrideData = [], ?User $user = null): Deviation
    {
        return DB::transaction(function () use ($laporanHarian, $overrideData, $user) {
            $user = $user ?? (auth()->user() ?? User::first());

            $machineName = $laporanHarian->machine?->name ?? 'Line '.($laporanHarian->line?->name ?? '1');
            $nomorBets = $laporanHarian->batch_number ?: ($laporanHarian->nomor_bets ?: ('BETS-'.date('Ymd').'-'.$laporanHarian->id));

            $topik = $overrideData['topik'] ?? "Penyimpangan Batch {$nomorBets}: Reject Kritis / Kerusakan Mesin {$machineName}";

            $deviationData = [
                'topik' => $topik,
                'department' => 'Produksi',
                'pic' => $user?->name ?? 'Supervisor Produksi',
                'nomor_bets' => $nomorBets,
                'nomor_alat' => $machineName,
                'jenis_penyimpangan' => $overrideData['jenis_penyimpangan'] ?? 'Proses',
                'identifikasi_penyimpangan' => $overrideData['identifikasi_penyimpangan'] ?? 'Mayor',
                'tanggal_temuan' => $laporanHarian->tanggal ? $laporanHarian->tanggal->toDateString() : now()->toDateString(),
                'is_production_stopped' => $laporanHarian->total_pause_menit > 30,
                'immediate_action_details' => $overrideData['immediate_action_details'] ?? "Eskalasi temuan dari Laporan Harian Produksi #{$laporanHarian->id}. Waktu henti: {$laporanHarian->total_pause_menit} menit, Reject: {$laporanHarian->total_reject} pcs.",
                'action' => 'draft',
                'custom_attributes' => [
                    'source_laporan_harian_id' => $laporanHarian->id,
                    'total_reject' => $laporanHarian->total_reject,
                    'total_pause_menit' => $laporanHarian->total_pause_menit,
                    'oee_persen' => $laporanHarian->oee_persen,
                ],
            ];

            $deviation = $this->deviationService->createDeviation($deviationData, $user);

            event(new ProductionDeviationReportCreatedEvent($laporanHarian, $deviation, $user));

            $this->notificationService->broadcast(
                moduleSlug: 'quality',
                type: 'deviation_spawned_from_production',
                title: "Draf Deviasi Baru dari Produksi ({$deviation->deviation_number})",
                message: "Deviasi kritis dilaporkan untuk Batch {$nomorBets} di {$machineName}. Menunggu investigasi QA.",
                actionUrl: '/quality/deviations',
                urgency: 'high'
            );

            return $deviation;
        });
    }

    /**
     * Synchronize production output, downtime, and material usage to Finance P&L and HRIS KPIs.
     */
    public function syncProductionBatchToFinanceAndHris(LaporanHarian $laporanHarian, ?User $user = null): array
    {
        return DB::transaction(function () use ($laporanHarian, $user) {
            $user = $user ?? (auth()->user() ?? User::first());

            // 1. Finance HPP & Budget P&L Flow
            $currentPeriod = FinPeriod::where('month', (int) now()->month)
                ->where('year', (int) now()->year)
                ->first() ?? FinPeriod::latest('id')->first();

            $outputFisik = (int) $laporanHarian->output_fisik;
            $totalReject = (int) $laporanHarian->total_reject;
            $cleanOutput = max(0, $outputFisik - $totalReject);

            // Update Finance monthly budget realization for Production department (Dept ID 2 = Produksi)
            $prodDeptBudget = $currentPeriod ? FinMonthlyBudget::where('period_id', $currentPeriod->id)
                ->where('department_id', 2)
                ->first() : null;

            if ($prodDeptBudget) {
                $estimatedLaborCost = round(($laporanHarian->waktu_bersih_menit / 60) * 50000, 2); // standard rate
                $prodDeptBudget->increment('realized_amount', $estimatedLaborCost);
            }

            // 2. HRIS KPI / Quality Objective Flow (Produksi Department Objective)
            $oee = (float) $laporanHarian->oee_persen;
            $objProduksi = HrisDepartmentObjective::where('department_id', 2)->first();

            if ($objProduksi && $oee > 0) {
                $this->strategicBscService->evaluateObjective(
                    objectiveId: $objProduksi->id,
                    periodMonth: (int) now()->month,
                    actualValue: $oee,
                    notes: "Update otomatis dari Laporan Harian Produksi Batch {$laporanHarian->nomor_bets}",
                    userId: $user->id
                );
            }

            return [
                'batch_id' => $laporanHarian->id,
                'clean_output' => $cleanOutput,
                'total_reject' => $totalReject,
                'oee_persen' => $oee,
                'finance_period_id' => $currentPeriod?->id,
                'hris_objective_updated' => (bool) $objProduksi,
            ];
        });
    }

    // =========================================================================
    // 5. HRIS <-> Finance
    // =========================================================================

    /**
     * Synchronize HRIS headcount (TKL/TKTL) and effective working hours to Finance BSC 19 Ratios.
     */
    public function syncHeadcountToFinance(int $periodId): FinHeadcount
    {
        return DB::transaction(function () use ($periodId) {
            $allActive = HrisEmployee::where('status', 'active')->get();
            $totalHeadcount = $allActive->count();
            if ($totalHeadcount === 0) {
                $totalHeadcount = 45; // fallback benchmark
            }

            // Direct Labor (TKL)
            $directLabor = $allActive->filter(function ($emp) {
                return ((int) $emp->department_id === 2) || str_contains(strtoupper((string) $emp->category), 'TKL');
            })->count();

            if ($directLabor === 0) {
                $directLabor = 28;
            }

            // Effective working hours
            $workingHours = (float) HrisAttendance::where('status', 'present')->sum('working_hours');
            if ($workingHours <= 0) {
                $workingHours = $totalHeadcount * 160.0; // Standard 160 hrs/month
            }

            // Overtime & Payroll
            $overtimeCost = (float) HrisOvertime::where('status', 'approved')->sum('calculated_amount');
            $totalPayroll = (float) HrisSalary::sum('take_home_pay');
            if ($totalPayroll <= 0) {
                $totalPayroll = $totalHeadcount * 5200000.0; // fallback standard UMR
            }

            $headcount = FinHeadcount::updateOrCreate(
                ['period_id' => $periodId],
                [
                    'total_headcount' => $totalHeadcount,
                    'direct_labor_count' => $directLabor,
                    'working_hours' => round($workingHours, 2),
                    'overtime_cost' => round($overtimeCost, 2),
                    'total_payroll' => round($totalPayroll, 2),
                ]
            );

            // Recalculate 19 BSC ratios
            $this->financialRatioService->calculateRatios($periodId);

            event(new HrisHeadcountSyncedEvent($headcount, $periodId));

            return $headcount;
        });
    }

    /**
     * Push published Finance metrics into HRIS Level 3 Quality Objectives.
     */
    public function publishFinanceMetricsToHris(int $periodId, array $metrics): int
    {
        return DB::transaction(function () use ($periodId, $metrics) {
            $period = FinPeriod::findOrFail($periodId);
            $month = (int) $period->month;

            $updatedCount = 0;
            $objectives = HrisDepartmentObjective::where('department_id', 1)->get(); // Finance Dept

            foreach ($objectives as $obj) {
                $metricKey = strtolower(str_replace(' ', '_', $obj->kpi_name));
                $val = $metrics[$metricKey] ?? ($metrics['GPM'] ?? ($metrics['net_profit'] ?? null));

                if ($val !== null) {
                    $this->strategicBscService->evaluateObjective(
                        objectiveId: $obj->id,
                        periodMonth: $month,
                        actualValue: (float) $val,
                        notes: "Sinkronisasi otomatis variabel finansial terpublikasi Periode {$period->period_name}",
                        userId: auth()->id() ?? User::first()?->id ?? 1
                    );
                    $updatedCount++;
                }
            }

            event(new FinanceMetricsPublishedEvent($periodId, $metrics, $updatedCount));

            return $updatedCount;
        });
    }
}
