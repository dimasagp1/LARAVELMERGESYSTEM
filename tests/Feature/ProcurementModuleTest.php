<?php

namespace Tests\Feature;

use App\Core\Models\Department;
use App\Core\Models\Vendor;
use App\Models\User;
use App\Modules\Procurement\Models\PrBon;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\Procurement\Models\PurchaseRequest;
use Carbon\Carbon;
use Database\Seeders\DefaultErpModulesSeeder;
use Database\Seeders\ProcurementModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected User $buyer;

    protected User $omUser;

    protected User $gmUser;

    protected Department $department;

    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultErpModulesSeeder::class);
        $this->seed(ProcurementModuleSeeder::class);

        $this->department = Department::first();
        $this->vendor = Vendor::first();

        $this->superAdmin = User::where('email', 'buyer@herbatech.com')->first();
        $this->superAdmin->assignRole('superadmin');

        $this->buyer = User::where('email', 'buyer@herbatech.com')->first();
        $this->omUser = User::firstOrCreate(
            ['email' => 'om_test@herbatech.com'],
            ['name' => 'OM Tester', 'password' => bcrypt('password'), 'department_id' => $this->department->id]
        );
        $this->omUser->assignRole('operational_manager');

        $this->gmUser = User::firstOrCreate(
            ['email' => 'gm_test@herbatech.com'],
            ['name' => 'GM Tester', 'password' => bcrypt('password'), 'department_id' => $this->department->id]
        );
        $this->gmUser->assignRole('general_manager');
    }

    public function test_procurement_overview_dashboard_is_accessible_and_renders_kpis(): void
    {
        $response = $this->actingAs($this->buyer)->get('/procurement');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/Procurement/Overview')
            ->has('kpis')
            ->has('pipelineCounts')
            ->has('qualityTargets')
            ->has('pendingApprovals')
            ->has('charts')
        );
    }

    public function test_can_create_purchase_request_with_items_and_calculate_total(): void
    {
        $payload = [
            'department_id' => $this->department->id,
            'request_date' => Carbon::now()->toDateString(),
            'purpose' => 'Bahan Baku Rutin Produksi (Raw Material)',
            'pr_type' => 'operational',
            'bypass_budget_check' => false,
            'items' => [
                [
                    'item_name' => 'Ekstrak Temulawak Curcuma High Grade',
                    'quantity' => 100,
                    'uom' => 'kg',
                    'estimated_price' => 250000,
                    'is_bon' => false,
                ],
                [
                    'item_name' => 'Botol Kaca Amber 100ml',
                    'quantity' => 5000,
                    'uom' => 'pcs',
                    'estimated_price' => 2800,
                    'is_bon' => false,
                ],
            ],
        ];

        $response = $this->actingAs($this->buyer)->post('/procurement/purchase-requests', $payload);

        $response->assertRedirect();

        $pr = PurchaseRequest::latest('id')->first();
        $this->assertNotNull($pr);
        $this->assertStringStartsWith('PR-', $pr->pr_number);
        $this->assertEquals(2, $pr->items()->count());
        // 100 * 250,000 = 25,000,000; 5,000 * 2,800 = 14,000,000; Total = 39,000,000
        $this->assertEquals(39000000, (float) $pr->total_amount);
    }

    public function test_granular_8_step_workflow_from_estimate_to_l1_om_and_l2_gm_approvals(): void
    {
        // 1. Create a single item PR
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-TEST-202611-0001',
            'user_id' => $this->buyer->id,
            'department_id' => $this->department->id,
            'request_date' => Carbon::now(),
            'purpose' => 'Bahan Baku Produksi',
            'pr_type' => 'operational',
            'status' => 'pending',
            'total_amount' => 10000000,
        ]);

        $item = PrItem::create([
            'purchase_request_id' => $pr->id,
            'item_name' => 'Bahan Baku Uji Alur',
            'quantity' => 100,
            'uom' => 'kg',
            'estimated_price' => 10000000,
            'status' => 'pending_estimate',
        ]);

        // Step 2: Procurement sets estimate & flags
        $resEst = $this->actingAs($this->buyer)->post("/procurement/items/{$item->id}/estimate", [
            'estimated_price' => 100000, // 100,000 * 100 = 10,000,000
            'rekap_po_odoo' => true,
            'is_incoming' => true,
            'need_holding_quotation' => false,
            'need_holding_delivery' => false,
            'is_bon' => false,
        ]);
        $resEst->assertSessionHasNoErrors();
        $item->refresh();
        $this->assertEquals('pending', $item->status);

        // Step 3: Approval L1 by OM
        $resL1 = $this->actingAs($this->omUser)->post("/procurement/items/{$item->id}/approve-l1", [
            'notes' => 'Disetujui OM untuk dilanjutkan negosiasi harga',
        ]);
        $resL1->assertSessionHasNoErrors();
        $item->refresh();
        $this->assertEquals('waiting_actual_price', $item->status);

        // Step 5: Procurement inputs actual price and delivery plans
        $resActual = $this->actingAs($this->buyer)->post("/procurement/items/{$item->id}/actual-price", [
            'actual_unit_price' => 95000,
            'vendor_name' => 'PT Mitra Supplier',
            'delivery_plans' => [
                ['plan_date' => Carbon::now()->addDays(3)->toDateString(), 'plan_quantity' => 100, 'notes' => 'Kirim utuh'],
            ],
        ]);
        $resActual->assertSessionHasNoErrors();
        $item->refresh();
        $this->assertEquals('waiting_gm', $item->status);
        $this->assertEquals(9500000, (float) $item->actual_price);

        // Step 6: Approval L2 by GM
        $resL2 = $this->actingAs($this->gmUser)->post("/procurement/items/{$item->id}/approve-l2", [
            'notes' => 'Disetujui GM, terbitkan PO',
        ]);
        $resL2->assertSessionHasNoErrors();
        $item->refresh();
        $this->assertEquals('ready_to_po', $item->status);

        // Step 7: Process PO
        $resPo = $this->actingAs($this->buyer)->post("/procurement/items/{$item->id}/process-po", [
            'po_number' => 'PO-TEST-202611-0001',
            'po_manual_odoo' => true,
            'po_manual_reason' => 'Input langsung',
        ]);
        $resPo->assertSessionHasNoErrors();
        $item->refresh();
        $this->assertEquals('ordered', $item->status);
        $this->assertEquals('PO-TEST-202611-0001', $item->po_number);
    }

    public function test_item_rejection_and_bulk_revision_creates_new_draft_pr(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-REJ-202611-0001',
            'user_id' => $this->buyer->id,
            'department_id' => $this->department->id,
            'request_date' => Carbon::now(),
            'purpose' => 'Pembelian ATK',
            'pr_type' => 'non_operational',
            'status' => 'pending',
            'total_amount' => 5000000,
        ]);

        $itemApproved = PrItem::create([
            'purchase_request_id' => $pr->id,
            'item_name' => 'Kertas A4 70gr Sidu',
            'quantity' => 10,
            'uom' => 'rim',
            'estimated_price' => 500000,
            'status' => 'ready_to_po',
        ]);

        $itemRejected = PrItem::create([
            'purchase_request_id' => $pr->id,
            'item_name' => 'Kursi Kerja Ergonomis Mewah',
            'quantity' => 2,
            'uom' => 'unit',
            'estimated_price' => 4500000,
            'status' => 'pending',
        ]);

        // 1. Reject item
        $this->actingAs($this->omUser)->post("/procurement/items/{$itemRejected->id}/reject", [
            'reason' => 'Melebihi standar fasilitas staf',
            'step' => 'om',
        ]);

        $itemRejected->refresh();
        $this->assertEquals('rejected_om', $itemRejected->status);

        // 2. Perform Bulk Revision
        $resRev = $this->actingAs($this->buyer)->post("/procurement/purchase-requests/{$pr->id}/bulk-revise", [
            'rejected_item_ids' => [$itemRejected->id],
        ]);
        $resRev->assertRedirect();

        // Check new PR is created with revision count 1 and parent link
        $revisedPr = PurchaseRequest::where('parent_pr_id', $pr->id)->first();
        $this->assertNotNull($revisedPr);
        $this->assertEquals(1, $revisedPr->revision_count);
        $this->assertEquals('draft', $revisedPr->status);
        $this->assertEquals(1, $revisedPr->items()->count());
        $this->assertEquals('draft', $revisedPr->items()->first()->status);
        $this->assertEquals('Kursi Kerja Ergonomis Mewah', $revisedPr->items()->first()->item_name);

        // Original PR still preserves the approved item
        $this->assertEquals('ready_to_po', $itemApproved->fresh()->status);
    }

    public function test_pr_bon_special_procurement_lifecycle_and_gm_decision(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-BON-202611-0001',
            'user_id' => $this->buyer->id,
            'department_id' => $this->department->id,
            'request_date' => Carbon::now(),
            'purpose' => 'Belanja Kas Bon',
            'pr_type' => 'operational',
            'status' => 'pending',
            'total_amount' => 1500000,
        ]);

        $bonItem = PrItem::create([
            'purchase_request_id' => $pr->id,
            'item_name' => 'Semen Instan & Pasir Cor Perbaikan Lantai Pabrik',
            'quantity' => 10,
            'uom' => 'sak',
            'estimated_price' => 1500000,
            'actual_price' => 1500000,
            'is_bon' => true,
            'status' => 'waiting_bon', // In staging queue
        ]);

        // 1. Procurement groups staged item into a new Bon file
        $resBon = $this->actingAs($this->buyer)->post('/procurement/pr-bons', [
            'vendor_name' => 'Toko Bangunan Berkah Cikarang',
            'bon_date' => Carbon::now()->toDateString(),
            'item_ids' => [$bonItem->id],
            'item_prices' => [$bonItem->id => 1500000],
        ]);
        $resBon->assertRedirect();

        $bon = PrBon::latest('id')->first();
        $this->assertNotNull($bon);
        $this->assertEquals('waiting_gm', $bon->status);
        $this->assertEquals(1500000, (float) $bon->total_amount);
        $this->assertEquals('waiting_gm_bon', $bonItem->fresh()->status);

        // 2. GM approves the Bon
        $resDecision = $this->actingAs($this->gmUser)->post("/procurement/pr-bons/{$bon->id}/decision", [
            'action' => 'approve_all',
            'note' => 'Disetujui GM belanja tunai kas kecil',
        ]);
        $resDecision->assertSessionHasNoErrors();

        $bon->refresh();
        $this->assertEquals('approved_gm', $bon->status);
        $this->assertEquals('ready_to_po', $bonItem->fresh()->status);
    }

    public function test_physical_delivery_and_partial_delivery_progress(): void
    {
        $pr = PurchaseRequest::create([
            'pr_number' => 'PR-DEL-202611-0001',
            'user_id' => $this->buyer->id,
            'department_id' => $this->department->id,
            'request_date' => Carbon::now(),
            'purpose' => 'Bahan Baku Produksi',
            'pr_type' => 'operational',
            'status' => 'ordered',
            'total_amount' => 5000000,
        ]);

        $item = PrItem::create([
            'purchase_request_id' => $pr->id,
            'item_name' => 'Plastik Shrink Film Botol',
            'quantity' => 100,
            'ready_quantity' => 0,
            'uom' => 'roll',
            'actual_price' => 5000000,
            'status' => 'ordered',
        ]);

        // 1. Partial delivery (40 roll)
        $resDel1 = $this->actingAs($this->buyer)->post("/procurement/items/{$item->id}/deliveries", [
            'delivered_quantity' => 40,
            'delivery_date' => Carbon::now()->toDateString(),
            'delivery_order_number' => 'SJ-001',
        ]);
        $resDel1->assertSessionHasNoErrors();
        $item->refresh();
        $this->assertEquals(40, (float) $item->ready_quantity);
        $this->assertEquals('ordered', $item->status); // Still ordered because 40 < 100

        // 2. Final delivery (60 roll -> completes order)
        $resDel2 = $this->actingAs($this->buyer)->post("/procurement/items/{$item->id}/deliveries", [
            'delivered_quantity' => 60,
            'delivery_date' => Carbon::now()->toDateString(),
            'delivery_order_number' => 'SJ-002',
        ]);
        $resDel2->assertSessionHasNoErrors();
        $item->refresh();
        $this->assertEquals(100, (float) $item->ready_quantity);
        $this->assertEquals('delivered', $item->status); // Status moves to delivered!
    }
}
