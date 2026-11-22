<?php

namespace Tests\Feature;

use App\Core\Models\Plant;
use App\Core\Models\Product;
use App\Core\Models\Vendor;
use App\Models\User;
use App\Modules\SupplyChain\Models\ScmGoodsReceipt;
use App\Modules\SupplyChain\Models\ScmInventoryStock;
use App\Modules\SupplyChain\Models\ScmMaterialUsage;
use App\Modules\SupplyChain\Models\ScmMrpRun;
use App\Modules\SupplyChain\Models\ScmQcInspection;
use Database\Seeders\CoreMasterDataSeeder;
use Database\Seeders\DefaultErpModulesSeeder;
use Database\Seeders\SupplyChainModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplyChainModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Plant $plant;

    protected Product $product;

    protected Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultErpModulesSeeder::class);
        $this->seed(CoreMasterDataSeeder::class);
        $this->seed(SupplyChainModuleSeeder::class);

        $this->user = User::where('email', 'admin@herbatech.com')->first()
            ?? User::where('email', 'scm@herbatech.com')->first();
        $this->plant = Plant::first();
        $this->product = Product::where('type', 'raw_material')->first();
        $this->vendor = Vendor::first();
    }

    public function test_supply_chain_overview_dashboard_renders(): void
    {
        $response = $this->actingAs($this->user)->get('/supply-chain');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/SupplyChain/Overview')
            ->has('metrics.kpis.total_valuation')
            ->has('metrics.kpis.low_stock_count')
            ->has('metrics.kpis.near_expiry_count')
            ->has('metrics.kpis.pending_qc_count')
            ->has('metrics.charts.valuation_by_type')
            ->has('metrics.charts.status_distribution')
            ->has('metrics.charts.movement_trend')
        );
    }

    public function test_mrp_dashboard_and_simulation_run(): void
    {
        $indexResponse = $this->actingAs($this->user)->get('/supply-chain/mrp');
        $indexResponse->assertStatus(200);
        $indexResponse->assertInertia(fn ($page) => $page
            ->component('Modules/SupplyChain/Mrp/Index')
            ->has('mrpRuns')
            ->has('plants')
        );

        $runResponse = $this->actingAs($this->user)->post('/supply-chain/mrp/run', [
            'plant_id' => $this->plant->id,
            'planning_horizon_months' => 3,
            'notes' => 'Test feature MRP calculation',
        ]);

        $runResponse->assertRedirect('/supply-chain/mrp');
        $runResponse->assertSessionHas('success');

        $latestRun = ScmMrpRun::latest()->first();
        $this->assertNotNull($latestRun);

        $showResponse = $this->actingAs($this->user)->get("/supply-chain/mrp/{$latestRun->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertInertia(fn ($page) => $page
            ->component('Modules/SupplyChain/Mrp/Show')
            ->has('mrpRun')
        );
    }

    public function test_inbound_goods_receipt_creates_quarantine_stock_and_qc_inspection(): void
    {
        $createPageResponse = $this->actingAs($this->user)->get('/supply-chain/goods-receipt/create');
        $createPageResponse->assertStatus(200);

        $postResponse = $this->actingAs($this->user)->post('/supply-chain/goods-receipt', [
            'vendor_id' => $this->vendor->id,
            'plant_id' => $this->plant->id,
            'delivery_order_number' => 'SJ-TEST-001',
            'po_number' => 'PO-TEST-001',
            'receipt_date' => now()->toDateString(),
            'notes' => 'Penerimaan uji coba batch jahe',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'item_name' => $this->product->name,
                    'received_qty' => 50,
                    'uom' => 'KG',
                    'batch_number' => 'LOT-TEST-01',
                    'expiry_date' => now()->addYears(2)->toDateString(),
                    'unit_price' => 150000,
                ],
            ],
        ]);

        $postResponse->assertSessionHas('success');

        $receipt = ScmGoodsReceipt::where('delivery_order_number', 'SJ-TEST-001')->first();
        $this->assertNotNull($receipt);
        $this->assertEquals('quarantine', $receipt->status);

        // Assert item placed in quarantine
        $this->assertDatabaseHas('scm_goods_receipt_items', [
            'goods_receipt_id' => $receipt->id,
            'product_id' => $this->product->id,
            'status' => 'quarantine',
        ]);

        // Assert pending QC inspection exists
        $this->assertDatabaseHas('scm_qc_inspections', [
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-TEST-01',
            'status' => 'pending',
        ]);

        // Assert PDF export works
        $pdfResponse = $this->actingAs($this->user)->get("/supply-chain/goods-receipt/{$receipt->id}/pdf");
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_qc_inspection_gatekeeper_transfers_stock_to_available_when_passed(): void
    {
        $inspection = ScmQcInspection::where('status', 'pending')->first();
        $this->assertNotNull($inspection);

        $inspectResponse = $this->actingAs($this->user)->post("/supply-chain/quarantine/{$inspection->id}/inspect", [
            'is_visual_passed' => true,
            'is_lab_passed' => true,
            'is_quantity_passed' => true,
            'is_docs_passed' => true,
            'sample_qty' => 2,
            'passed_qty' => 50,
            'rejected_qty' => 0,
            'notes' => 'Lulus seluruh parameter CPOTB',
        ]);

        $inspectResponse->assertSessionHas('success');

        $inspection->refresh();
        $this->assertEquals('passed', $inspection->status);
        $this->assertEquals(50, $inspection->passed_qty);

        // Check stock movement recorded
        $this->assertDatabaseHas('scm_stock_movements', [
            'product_id' => $inspection->product_id,
            'reference_type' => 'transfer',
        ]);
    }

    public function test_qc_inspection_reject_generates_material_return(): void
    {
        // Create a new pending inspection
        $receipt = ScmGoodsReceipt::create([
            'grn_number' => 'GR-TEST-REJ-01',
            'vendor_id' => $this->vendor->id,
            'plant_id' => $this->plant->id,
            'delivery_order_number' => 'SJ-REJ-001',
            'receipt_date' => now()->toDateString(),
            'received_by' => $this->user->id,
            'status' => 'quarantine',
        ]);

        $item = $receipt->items()->create([
            'product_id' => $this->product->id,
            'item_name' => $this->product->name,
            'received_qty' => 30,
            'passed_qty' => 0,
            'rejected_qty' => 0,
            'uom' => 'KG',
            'batch_number' => 'LOT-REJ-001',
            'expiry_date' => now()->addYear()->toDateString(),
            'unit_price' => 100000,
            'status' => 'quarantine',
        ]);

        $inspection = ScmQcInspection::create([
            'inspection_number' => 'QC-TEST-REJ-01',
            'goods_receipt_item_id' => $item->id,
            'product_id' => $this->product->id,
            'batch_number' => 'LOT-REJ-001',
            'sample_qty' => 2,
            'inspector_id' => $this->user->id,
            'inspection_date' => now()->toDateString(),
            'is_visual_passed' => false,
            'is_lab_passed' => false,
            'is_quantity_passed' => true,
            'is_docs_passed' => false,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)->post("/supply-chain/quarantine/{$inspection->id}/inspect", [
            'is_visual_passed' => false,
            'is_lab_passed' => false,
            'is_quantity_passed' => true,
            'is_docs_passed' => false,
            'sample_qty' => 2,
            'passed_qty' => 0,
            'rejected_qty' => 30,
            'notes' => 'Cemaran mikroba di atas ambang batas',
        ]);

        $response->assertSessionHas('success');

        // Material Return Memo must have been generated
        $this->assertDatabaseHas('scm_material_returns', [
            'goods_receipt_id' => $receipt->id,
            'vendor_id' => $this->vendor->id,
        ]);
    }

    public function test_material_usage_bstb_deducts_inventory_stock(): void
    {
        $stock = ScmInventoryStock::where('status', 'available')
            ->where('quantity_available', '>=', 20)
            ->first();
        $this->assertNotNull($stock);

        $initialAvailable = (float) $stock->quantity_available;

        $response = $this->actingAs($this->user)->post('/supply-chain/material-usage', [
            'work_order_number' => 'SPK-TEST-999',
            'plant_id' => $this->plant->id,
            'department_id' => 1,
            'usage_date' => now()->toDateString(),
            'notes' => 'Penyerahan bahan uji coba',
            'items' => [
                [
                    'inventory_stock_id' => $stock->id,
                    'product_id' => $stock->product_id,
                    'batch_number' => $stock->batch_number,
                    'quantity' => 10,
                    'uom' => 'KG',
                    'unit_cost' => 180000,
                ],
            ],
        ]);

        $response->assertSessionHas('success');

        $usage = ScmMaterialUsage::where('work_order_number', 'SPK-TEST-999')->first();
        $this->assertNotNull($usage);

        // Verify stock was deducted
        $stock->refresh();
        $this->assertEquals($initialAvailable - 10, (float) $stock->quantity_available);

        // PDF export
        $pdfResponse = $this->actingAs($this->user)->get("/supply-chain/material-usage/{$usage->id}/pdf");
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_three_way_matching_audit_and_vendor_bill_generation(): void
    {
        $receipt = ScmGoodsReceipt::where('status', 'completed')->first();
        $this->assertNotNull($receipt);

        $passedQty = (float) $receipt->items->sum('passed_qty');
        $unitPrice = (float) $receipt->items->first()->unit_price;
        $expectedAmount = $passedQty * $unitPrice;

        $response = $this->actingAs($this->user)->post('/supply-chain/matching', [
            'goods_receipt_id' => $receipt->id,
            'vendor_invoice_number' => 'INV-AUDIT-001',
            'invoice_qty' => $passedQty,
            'invoice_amount' => $expectedAmount,
            'auto_create_bill' => true,
        ]);

        $response->assertSessionHas('success');

        // Assert 3-Way Matching created
        $this->assertDatabaseHas('scm_three_way_matchings', [
            'goods_receipt_id' => $receipt->id,
            'vendor_invoice_number' => 'INV-AUDIT-001',
            'status' => 'approved',
        ]);

        // Assert Vendor Bill created
        $this->assertDatabaseHas('scm_vendor_bills', [
            'invoice_number' => 'INV-AUDIT-001',
            'status' => 'approved',
        ]);
    }

    public function test_multi_warehouse_stock_matrix_renders(): void
    {
        $response = $this->actingAs($this->user)->get('/supply-chain/stock');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/SupplyChain/Stock/Index')
            ->has('stocks')
            ->has('recentMovements')
            ->has('plants')
            ->has('locations')
        );
    }
}
