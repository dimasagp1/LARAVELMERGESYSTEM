<?php

namespace Tests\Feature;

use App\Core\Integrations\Contracts\FinanceBudgetIntegrationContract;
use App\Core\Integrations\Contracts\HrisIntegrationContract;
use App\Core\Integrations\Contracts\ProductionIntegrationContract;
use App\Core\Integrations\Contracts\RndIntegrationContract;
use App\Core\Integrations\Contracts\SupplyChainIntegrationContract;
use App\Core\Integrations\Contracts\UnifiedApprovalContract;
use App\Core\Integrations\Events\FormulaPromotedToBomEvent;
use App\Core\Integrations\Services\NotificationCenterService;
use App\Core\Models\Bom;
use App\Core\Models\Department;
use App\Core\Models\Location;
use App\Core\Models\Plant;
use App\Core\Models\Product;
use App\Core\Models\Uom;
use App\Core\Models\Vendor;
use App\Models\User;
use App\Modules\Finance\Models\FinFiscalYear;
use App\Modules\Finance\Models\FinHeadcount;
use App\Modules\Finance\Models\FinMonthlyBudget;
use App\Modules\Finance\Models\FinPeriod;
use App\Modules\Hris\Models\HrisEmployee;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\Procurement\Models\PurchaseRequest;
use App\Modules\Production\Models\LaporanHarian;
use App\Modules\Production\Models\ProductionLine;
use App\Modules\Production\Models\WeeklyPlan;
use App\Modules\Quality\Models\Deviation;
use App\Modules\Rnd\Models\Formula;
use App\Modules\Rnd\Models\FormulaItem;
use App\Modules\SupplyChain\Models\ScmGoodsReceipt;
use App\Modules\SupplyChain\Models\ScmGoodsReceiptItem;
use App\Modules\SupplyChain\Models\ScmMrpItem;
use App\Modules\SupplyChain\Models\ScmMrpRun;
use App\Modules\SupplyChain\Models\ScmQcInspection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CrossModuleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Plant $plant;

    protected Department $department;

    protected Uom $uom;

    protected Product $productFg;

    protected Product $productRm;

    protected Vendor $vendor;

    protected FinPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'admin@herbatech.com',
            'name' => 'Super Administrator',
        ]);

        $this->actingAs($this->user);

        $this->plant = Plant::create([
            'code' => 'PLANT-01',
            'name' => 'Pabrik Ekstraksi Boyolali',
            'is_active' => true,
        ]);

        Location::create([
            'plant_id' => $this->plant->id,
            'code' => 'WH-QRN',
            'name' => 'Gudang Karantina',
            'type' => 'warehouse',
            'is_quarantine' => true,
            'is_active' => true,
        ]);

        Location::create([
            'plant_id' => $this->plant->id,
            'code' => 'WH-RAW',
            'name' => 'Gudang Bahan Baku Utama',
            'type' => 'warehouse',
            'is_quarantine' => false,
            'is_active' => true,
        ]);

        $this->department = Department::create([
            'code' => 'RND',
            'name' => 'Research & Development',
            'is_active' => true,
        ]);

        $this->uom = Uom::create([
            'code' => 'KG',
            'name' => 'Kilogram',
            'symbol' => 'Kg',
        ]);

        $this->productFg = Product::create([
            'sku' => 'FG-CURCUMA-01',
            'code' => 'FG-01',
            'name' => 'Kapsul Ekstrak Temulawak',
            'type' => 'fg',
            'uom_id' => $this->uom->id,
            'standard_cost' => 25000,
            'is_active' => true,
        ]);

        $this->productRm = Product::create([
            'sku' => 'RM-CURCUMA-RAW',
            'code' => 'RM-01',
            'name' => 'Simplisia Rimpang Temulawak',
            'type' => 'rm',
            'uom_id' => $this->uom->id,
            'standard_cost' => 50000,
            'is_active' => true,
        ]);

        $this->vendor = Vendor::create([
            'code' => 'VEND-001',
            'name' => 'PT Bio Herbal Nusantara',
            'is_active' => true,
        ]);

        $fy = FinFiscalYear::create([
            'year' => 2026,
            'name' => 'Tahun Buku 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'open',
        ]);

        $this->period = FinPeriod::create([
            'fiscal_year_id' => $fy->id,
            'period_num' => 11,
            'period_name' => 'November 2026',
            'month' => 11,
            'year' => 2026,
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-30',
            'status' => 'open',
        ]);
    }

    /**
     * Requirement 1: R&D Formula Approved -> Promoted to Master BOM in Core Shared Kernel.
     */
    public function test_rnd_formula_promotes_to_master_bom_in_core_shared_kernel(): void
    {
        Event::fake([FormulaPromotedToBomEvent::class]);

        $formula = Formula::create([
            'code' => 'FORMULA-CURCUMA-V1',
            'product_id' => $this->productFg->id,
            'version' => 1,
            'target_serving_size_g' => 500.0,
            'servings_per_pack' => 10,
            'total_percentage' => 100.0,
            'total_cost_per_kg' => 75000,
            'total_cost_per_pack' => 37500,
            'stage' => 'plant_trial',
            'status' => 'approved',
            'created_by' => $this->user->id,
            'approved_by_gm' => $this->user->id,
        ]);

        FormulaItem::create([
            'formula_id' => $formula->id,
            'material_product_id' => $this->productRm->id,
            'vendor_id' => $this->vendor->id,
            'percentage' => 100.0,
            'weight_per_serving_g' => 500.0,
            'price_per_kg' => 50000,
            'price_per_serving' => 25000,
        ]);

        $response = $this->post(route('rnd.formulas.promote-to-bom', $formula->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('boms', [
            'product_id' => $this->productFg->id,
            'version' => 'V1',
            'is_active' => true,
            'status' => 'active',
        ]);

        $bom = Bom::where('product_id', $this->productFg->id)->first();
        $this->assertNotNull($bom);
        $this->assertEquals(5000.0, (float) $bom->batch_size);

        $this->assertDatabaseHas('bom_items', [
            'bom_id' => $bom->id,
            'product_id' => $this->productRm->id,
            'percentage' => 100.0,
        ]);

        Event::assertDispatched(FormulaPromotedToBomEvent::class);
    }

    /**
     * Requirement 1: Procurement PO approved stages raw material price to R&D.
     */
    public function test_po_approved_updates_rnd_price_staging_and_item_prices(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-202611-001',
            'user_id' => $this->user->id,
            'department_id' => $this->department->id,
            'request_date' => now(),
            'purpose' => 'Pengadaan Bahan Baku',
            'pr_type' => 'operational',
            'status' => 'pending',
            'total_amount' => 110000000,
        ]);

        $item = PrItem::create([
            'purchase_request_id' => $pr->id,
            'product_id' => $this->productRm->id,
            'item_name' => $this->productRm->name,
            'quantity' => 2000,
            'uom' => 'Kg',
            'estimated_price' => 100000000,
            'actual_price' => 110000000, // Rp 55.000 / Kg
            'vendor_id' => $this->vendor->id,
            'status' => 'ready_to_po',
        ]);

        $rndIntegration = app(RndIntegrationContract::class);
        $staging = $rndIntegration->stageRawMaterialPrice(
            productId: $this->productRm->id,
            newPrice: 55000,
            poNumber: 'PO-202611-999',
            vendorId: $this->vendor->id,
            user: $this->user
        );

        $this->assertEquals(55000, (float) $staging->new_price);
        $this->assertDatabaseHas('rnd_price_stagings', [
            'product_id' => $this->productRm->id,
            'po_number' => 'PO-202611-999',
            'new_price' => 55000,
        ]);

        $this->productRm->refresh();
        $this->assertEquals(55000, (float) $this->productRm->standard_cost);
    }

    /**
     * Requirement 2: SCM MRP net requirements convert to Draft PR in Procurement.
     */
    public function test_mrp_net_requirements_convert_to_draft_pr_in_procurement(): void
    {
        $mrpRun = ScmMrpRun::create([
            'run_number' => 'MRP-202611-0001',
            'period_name' => 'November 2026',
            'status' => 'simulated',
            'executed_by' => $this->user->id,
        ]);

        $mrpItem = ScmMrpItem::create([
            'mrp_run_id' => $mrpRun->id,
            'product_id' => $this->productRm->id,
            'gross_requirement' => 1500,
            'on_hand_stock' => 500,
            'allocated_stock' => 0,
            'outstanding_po_qty' => 0,
            'safety_stock' => 200,
            'net_requirement' => 1200, // Net need = 1200 Kg
            'status' => 'pending',
        ]);

        $response = $this->post(route('supply-chain.mrp.convert-to-pr', $mrpRun->id));
        $response->assertRedirect(route('procurement.purchase-requests.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('purchase_requests', [
            'pr_type' => 'operational',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('pr_items', [
            'product_id' => $this->productRm->id,
            'quantity' => 1200,
            'status' => 'draft',
        ]);

        $mrpItem->refresh();
        $this->assertEquals('converted_to_pr', $mrpItem->status);
    }

    /**
     * Requirement 2: PR submission validates Finance Budget Gatekeeper commitment & release.
     */
    public function test_pr_submission_validates_finance_budget_gatekeeper_commitment_and_release(): void
    {
        $finBudget = FinMonthlyBudget::create([
            'period_id' => $this->period->id,
            'department_id' => $this->department->id,
            'fiscal_year_id' => $this->period->fiscal_year_id,
            'initial_budget' => 50000000,
            'realized_amount' => 0,
            'booked_amount' => 0,
        ]);

        $budgetContract = app(FinanceBudgetIntegrationContract::class);

        // 1. Check budget (sufficient)
        $check = $budgetContract->checkBudget($this->department->id, 20000000, $this->period->id);
        $this->assertTrue($check['allow_proceed']);

        // 2. Commit budget (book)
        $commit = $budgetContract->commitBudget($this->department->id, 20000000, $this->period->id);
        $this->assertTrue($commit['success']);

        $finBudget->refresh();
        $this->assertEquals(20000000, (float) $finBudget->booked_amount);
        $this->assertEquals(30000000, (float) $finBudget->remainingBudget());

        // 3. Release budget (unbook upon rejection)
        $budgetContract->releaseBudget($this->department->id, 20000000, $this->period->id);
        $finBudget->refresh();
        $this->assertEquals(0, (float) $finBudget->booked_amount);
        $this->assertEquals(50000000, (float) $finBudget->remainingBudget());
    }

    /**
     * Requirement 3: PO approved opens Goods Receipt in Quarantine SCM.
     */
    public function test_po_approved_opens_goods_receipt_in_quarantine_scm(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-202611-002',
            'user_id' => $this->user->id,
            'department_id' => $this->department->id,
            'request_date' => now(),
            'purpose' => 'Pembelian Simplisia',
            'pr_type' => 'operational',
            'status' => 'pending',
            'total_amount' => 50000000,
        ]);

        $item = PrItem::create([
            'purchase_request_id' => $pr->id,
            'product_id' => $this->productRm->id,
            'item_name' => $this->productRm->name,
            'quantity' => 1000,
            'uom' => 'Kg',
            'estimated_price' => 50000000,
            'actual_price' => 50000000,
            'vendor_id' => $this->vendor->id,
            'po_number' => 'PO-202611-AUTO',
            'status' => 'ordered',
        ]);

        $scmIntegration = app(SupplyChainIntegrationContract::class);
        $receipt = $scmIntegration->openGoodsReceiptFromPo($item, $this->user, $this->plant->id);

        $this->assertNotNull($receipt);
        $this->assertEquals('quarantine', $receipt->status);
        $this->assertEquals('PO-202611-AUTO', $receipt->po_number);

        $this->assertDatabaseHas('scm_goods_receipts', [
            'po_number' => 'PO-202611-AUTO',
            'status' => 'quarantine',
        ]);

        $this->assertDatabaseHas('scm_goods_receipt_items', [
            'goods_receipt_id' => $receipt->id,
            'product_id' => $this->productRm->id,
            'received_qty' => 1000,
            'status' => 'quarantine',
        ]);
    }

    /**
     * Requirement 3: QC inspection passed unlocks 3-Way Matching in Finance/SCM.
     */
    public function test_qc_inspection_passed_transfers_stock_and_unlocks_three_way_matching(): void
    {
        $receipt = ScmGoodsReceipt::create([
            'grn_number' => 'GR-202611-TEST',
            'po_number' => 'PO-202611-TEST',
            'vendor_id' => $this->vendor->id,
            'plant_id' => $this->plant->id,
            'delivery_order_number' => 'SJ-12345',
            'receipt_date' => now()->toDateString(),
            'received_by' => $this->user->id,
            'status' => 'quarantine',
        ]);

        $receiptItem = ScmGoodsReceiptItem::create([
            'goods_receipt_id' => $receipt->id,
            'product_id' => $this->productRm->id,
            'item_name' => $this->productRm->name,
            'received_qty' => 500,
            'passed_qty' => 0,
            'rejected_qty' => 0,
            'uom' => 'Kg',
            'unit_price' => 50000,
            'status' => 'quarantine',
        ]);

        $inspection = ScmQcInspection::create([
            'inspection_number' => 'QC-202611-TEST',
            'goods_receipt_item_id' => $receiptItem->id,
            'product_id' => $this->productRm->id,
            'sample_qty' => 25,
            'passed_qty' => 0,
            'rejected_qty' => 0,
            'inspector_id' => $this->user->id,
            'inspection_date' => now()->toDateString(),
            'is_visual_passed' => true,
            'is_lab_passed' => true,
            'is_quantity_passed' => true,
            'is_docs_passed' => true,
            'status' => 'passed',
        ]);

        $scmIntegration = app(SupplyChainIntegrationContract::class);
        $result = $scmIntegration->handleQcInspectionResult($inspection, $this->user);

        $this->assertTrue($result['ready_for_matching']);
        $this->assertEquals('passed', $result['status']);
    }

    /**
     * Requirement 4: Production Laporan Harian spawns QMS Deviation Report.
     */
    public function test_production_laporan_harian_spawns_qms_deviation_report(): void
    {
        $line = ProductionLine::create([
            'line_number' => 'LINE-01',
            'name' => 'Lini Mixing Ekstraksi',
            'plant_id' => $this->plant->id,
            'is_active' => true,
        ]);

        $weeklyPlan = WeeklyPlan::create([
            'plan_number' => 'WPS-202611-0001',
            'produk_id' => $this->productFg->id,
            'line_id' => $line->id,
            'proses' => 'mixing',
            'batch_number' => 'BETS-202611-EX01',
            'target_output' => 1000,
            'tanggal' => now()->toDateString(),
            'shift' => 'shift1',
            'status' => 'aktif',
            'created_by' => $this->user->id,
        ]);

        $laporan = LaporanHarian::create([
            'report_number' => 'LHP-202611-001',
            'weekly_plan_id' => $weeklyPlan->id,
            'batch_number' => 'BETS-202611-EX01',
            'tanggal' => now()->toDateString(),
            'shift' => 'shift1',
            'proses' => 'mixing',
            'line_id' => $line->id,
            'produk_id' => $this->productFg->id,
            'user_id' => $this->user->id,
            'status' => 'draft',
            'output_fisik' => 1000,
            'total_reject' => 250, // 25% reject rate -> high deviation
            'total_pause_menit' => 45,
            'oee_persen' => 65.5,
        ]);

        $prodIntegration = app(ProductionIntegrationContract::class);
        $deviation = $prodIntegration->spawnQualityDeviation($laporan, [
            'topik' => 'Penyimpangan Reject 25% pada Batch BETS-202611-EX01',
            'identifikasi_penyimpangan' => 'Mayor',
        ], $this->user);

        $this->assertInstanceOf(Deviation::class, $deviation);
        $this->assertEquals('Produksi', $deviation->department);
        $this->assertEquals('BETS-202611-EX01', $deviation->nomor_bets);
        $this->assertEquals('DRAFT', $deviation->status);

        $this->assertDatabaseHas('quality_deviations', [
            'nomor_bets' => 'BETS-202611-EX01',
            'department' => 'Produksi',
            'status' => 'DRAFT',
        ]);
    }

    /**
     * Requirement 5: HRIS Headcount & Working Hours Sync to Finance 19 BSC Productivity Ratios.
     */
    public function test_hris_headcount_and_hours_sync_to_finance_19_bsc_ratios(): void
    {
        // Seed HRIS Employees with user accounts
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        HrisEmployee::create([
            'nik' => '3309012345670001',
            'user_id' => $user1->id,
            'department_id' => $this->department->id,
            'position' => 'Operator Produksi',
            'join_date' => '2024-01-01',
            'status' => 'active',
            'category' => 'TKL',
        ]);

        HrisEmployee::create([
            'nik' => '3309012345670002',
            'user_id' => $user2->id,
            'department_id' => $this->department->id,
            'position' => 'Staff Accounting',
            'join_date' => '2024-01-01',
            'status' => 'active',
            'category' => 'TKTL',
        ]);

        $hrisIntegration = app(HrisIntegrationContract::class);
        $headcount = $hrisIntegration->syncHeadcountToFinance($this->period->id);

        $this->assertInstanceOf(FinHeadcount::class, $headcount);
        $this->assertDatabaseHas('fin_headcounts', [
            'period_id' => $this->period->id,
        ]);

        $this->assertGreaterThanOrEqual(2, $headcount->total_headcount);
    }

    /**
     * Requirement 6: Unified Approval Inbox aggregates cross-module pending items.
     */
    public function test_unified_approval_inbox_aggregates_cross_module_pending_items(): void
    {
        $approvalService = app(UnifiedApprovalContract::class);
        $approvals = $approvalService->getPendingApprovalsForUser($this->user);

        $this->assertIsArray($approvals);
        $badgeCounts = $approvalService->getLiveModuleBadgeCounts();
        $this->assertIsArray($badgeCounts);
        $this->assertArrayHasKey('procurement', $badgeCounts);
        $this->assertArrayHasKey('supply-chain', $badgeCounts);
        $this->assertArrayHasKey('quality', $badgeCounts);
        $this->assertArrayHasKey('rnd', $badgeCounts);
    }

    /**
     * Requirement 6: Notification Center API delivers real-time alerts.
     */
    public function test_notification_center_api_and_realtime_alerts(): void
    {
        $notificationService = app(NotificationCenterService::class);
        $notification = $notificationService->send(
            moduleSlug: 'procurement',
            type: 'budget_alert',
            title: 'Peringatan Pagu Anggaran',
            message: 'Pagu anggaran Departemen R&D tersisa 15%.',
            actionUrl: '/procurement/purchase-requests',
            urgency: 'high',
            userId: $this->user->id
        );

        $this->assertDatabaseHas('erp_notifications', [
            'id' => $notification->id,
            'is_read' => false,
        ]);

        // Test API index
        $response = $this->getJson(route('notifications.index'));
        $response->assertOk();
        $response->assertJsonStructure([
            'notifications',
            'unread_count',
            'total',
        ]);
        $this->assertEquals(1, $response->json('unread_count'));

        // Test API mark as read
        $markResponse = $this->postJson(route('notifications.read', $notification->id));
        $markResponse->assertOk();
        $this->assertTrue($markResponse->json('success'));

        $notification->refresh();
        $this->assertTrue($notification->is_read);
    }
}
