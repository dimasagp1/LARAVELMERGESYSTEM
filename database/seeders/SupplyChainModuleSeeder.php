<?php

namespace Database\Seeders;

use App\Core\Models\Department;
use App\Core\Models\Location;
use App\Core\Models\Plant;
use App\Core\Models\Product;
use App\Core\Models\Uom;
use App\Core\Models\Vendor;
use App\Models\User;
use App\Modules\SupplyChain\Models\ScmForecast;
use App\Modules\SupplyChain\Models\ScmGoodsReceipt;
use App\Modules\SupplyChain\Models\ScmGoodsReceiptItem;
use App\Modules\SupplyChain\Models\ScmInventoryStock;
use App\Modules\SupplyChain\Models\ScmMaterialReturn;
use App\Modules\SupplyChain\Models\ScmMaterialReturnItem;
use App\Modules\SupplyChain\Models\ScmMaterialUsage;
use App\Modules\SupplyChain\Models\ScmMaterialUsageItem;
use App\Modules\SupplyChain\Models\ScmMrpItem;
use App\Modules\SupplyChain\Models\ScmMrpRun;
use App\Modules\SupplyChain\Models\ScmProductionSchedule;
use App\Modules\SupplyChain\Models\ScmQcInspection;
use App\Modules\SupplyChain\Models\ScmSalesData;
use App\Modules\SupplyChain\Models\ScmStockMovement;
use App\Modules\SupplyChain\Models\ScmThreeWayMatching;
use App\Modules\SupplyChain\Models\ScmVendorBill;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SupplyChainModuleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Spatie Permissions & Roles
        $permissions = [
            'scm.view',
            'scm.mrp.run',
            'scm.goods_receipt.create',
            'scm.qc.inspect',
            'scm.material_usage.create',
            'scm.material_return.create',
            'scm.matching.audit',
            'scm.stock.view',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        $superadmin = Role::firstOrCreate(['name' => 'Superadmin']);
        $superadmin->givePermissionTo($permissions);

        $ppicRole = Role::firstOrCreate(['name' => 'production_planning']);
        $ppicRole->givePermissionTo(['scm.view', 'scm.mrp.run', 'scm.stock.view']);

        $qcRole = Role::firstOrCreate(['name' => 'quality_control']);
        $qcRole->givePermissionTo(['scm.view', 'scm.qc.inspect', 'scm.material_return.create']);

        $whRole = Role::firstOrCreate(['name' => 'inventory_control']);
        $whRole->givePermissionTo(['scm.view', 'scm.goods_receipt.create', 'scm.material_usage.create', 'scm.stock.view']);

        $user = User::first() ?? User::factory()->create(['name' => 'Admin SCM', 'email' => 'scm@herbatech.com']);

        // 2. Plants & Locations
        $plant = Plant::firstOrCreate(
            ['code' => 'PLANT-01'],
            [
                'name' => 'Pabrik Ekstrak & Formulasi Sukoharjo',
                'address' => 'Kawasan Industri Herbal Cikarang/Sukoharjo',
                'is_active' => true,
            ]
        );

        $locRaw = Location::firstOrCreate(
            ['plant_id' => $plant->id, 'code' => 'WH-RAW'],
            ['name' => 'Gudang Utama Raw Material', 'type' => 'warehouse', 'is_quarantine' => false]
        );

        $locQrn = Location::firstOrCreate(
            ['plant_id' => $plant->id, 'code' => 'WH-QRN'],
            ['name' => 'Gudang Karantina Masuk QC', 'type' => 'quarantine', 'is_quarantine' => true]
        );

        $locFg = Location::firstOrCreate(
            ['plant_id' => $plant->id, 'code' => 'WH-FG'],
            ['name' => 'Gudang Produk Jadi (FG)', 'type' => 'warehouse', 'is_quarantine' => false]
        );

        $deptProd = Department::firstOrCreate(
            ['code' => 'PROD'],
            ['name' => 'Production & Manufacturing']
        );

        // 3. UOMs
        $kg = Uom::firstOrCreate(['code' => 'KG'], ['name' => 'Kilogram', 'category' => 'weight', 'conversion_factor' => 1.0, 'is_active' => true]);
        $pcs = Uom::firstOrCreate(['code' => 'PCS'], ['name' => 'Pieces', 'category' => 'quantity', 'conversion_factor' => 1.0, 'is_active' => true]);
        $sachet = Uom::firstOrCreate(['code' => 'SACHET'], ['name' => 'Sachet', 'category' => 'quantity', 'conversion_factor' => 1.0, 'is_active' => true]);

        // 4. Vendors
        $vendor1 = Vendor::firstOrCreate(
            ['code' => 'VEN-EXT-01'],
            [
                'name' => 'PT Sumber Alam Ekstrak Herbal',
                'contact_person' => 'Haji Suryo',
                'phone' => '081234567890',
                'email' => 'suryo@sumberalam.co.id',
                'is_active' => true,
            ]
        );

        $vendor2 = Vendor::firstOrCreate(
            ['code' => 'VEN-PKG-01'],
            [
                'name' => 'PT Foil Packaging Prima',
                'contact_person' => 'Ibu Lilis',
                'phone' => '081987654321',
                'email' => 'sales@foilprima.com',
                'is_active' => true,
            ]
        );

        // 5. Products (Raw Materials, Packaging, Finished Goods)
        $rmJahe = Product::firstOrCreate(
            ['sku' => 'RM-JAHE-01'],
            [
                'name' => 'Ekstrak Jahe Merah Murni (Zingiber officinale)',
                'type' => 'raw_material',
                'uom_id' => $kg->id,
                'standard_cost' => 180000,
                'min_stock' => 150,
                'safety_stock' => 150,
                'is_active' => true,
            ]
        );

        $rmMadu = Product::firstOrCreate(
            ['sku' => 'RM-MADU-01'],
            [
                'name' => 'Madu Hutan Sumbawa Grade A',
                'type' => 'raw_material',
                'uom_id' => $kg->id,
                'standard_cost' => 85000,
                'min_stock' => 200,
                'safety_stock' => 200,
                'is_active' => true,
            ]
        );

        $rmMeniran = Product::firstOrCreate(
            ['sku' => 'RM-MENIRAN-01'],
            [
                'name' => 'Ekstrak Meniran Kering (Phyllanthus niruri)',
                'type' => 'raw_material',
                'uom_id' => $kg->id,
                'standard_cost' => 220000,
                'min_stock' => 80,
                'safety_stock' => 80,
                'is_active' => true,
            ]
        );

        $pmSachet = Product::firstOrCreate(
            ['sku' => 'PM-SACHET-01'],
            [
                'name' => 'Alufoil Sachet Roll 15ml HerbalMed',
                'type' => 'packaging',
                'uom_id' => $pcs->id,
                'standard_cost' => 450,
                'min_stock' => 50000,
                'safety_stock' => 50000,
                'is_active' => true,
            ]
        );

        $fgTolakAngin = Product::firstOrCreate(
            ['sku' => 'FG-HERBA-01'],
            [
                'name' => 'HerbaMed Cair Plus Madu Sachet 15ml',
                'type' => 'finished_good',
                'uom_id' => $sachet->id,
                'standard_cost' => 2800,
                'min_stock' => 10000,
                'safety_stock' => 10000,
                'is_active' => true,
            ]
        );

        // 6. Sales Data & Forecasts
        for ($m = 1; $m <= 10; $m++) {
            ScmSalesData::firstOrCreate(
                [
                    'product_id' => $fgTolakAngin->id,
                    'year' => 2026,
                    'month' => $m,
                ],
                [
                    'quantity_sold' => rand(15000, 28000),
                    'revenue_amount' => rand(45000000, 84000000),
                ]
            );
        }

        ScmForecast::firstOrCreate(
            [
                'product_id' => $fgTolakAngin->id,
                'type' => 'annual',
                'period_year' => 2026,
            ],
            [
                'forecast_quantity' => 300000,
                'forecast_value' => 840000000,
                'created_by' => $user->id,
            ]
        );

        // 7. Production Schedules (MPS)
        $mps1 = ScmProductionSchedule::firstOrCreate(
            ['schedule_number' => 'MPS-202611-0001'],
            [
                'plant_id' => $plant->id,
                'product_id' => $fgTolakAngin->id,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(10)->toDateString(),
                'planned_quantity' => 25000,
                'status' => 'confirmed',
                'notes' => 'Batch reguler pemenuhan stok sachet herbal',
                'created_by' => $user->id,
            ]
        );

        $mps2 = ScmProductionSchedule::firstOrCreate(
            ['schedule_number' => 'MPS-202611-0002'],
            [
                'plant_id' => $plant->id,
                'product_id' => $fgTolakAngin->id,
                'start_date' => now()->addDays(15)->toDateString(),
                'end_date' => now()->addDays(20)->toDateString(),
                'planned_quantity' => 30000,
                'status' => 'confirmed',
                'notes' => 'Batch ekspor farmasi herbal',
                'created_by' => $user->id,
            ]
        );

        // 8. Physical Inventory Stocks
        // Available Raw Materials in WH-RAW
        ScmInventoryStock::updateOrCreate(
            ['product_id' => $rmJahe->id, 'location_id' => $locRaw->id, 'batch_number' => 'LOT-JH-202609'],
            [
                'plant_id' => $plant->id,
                'expiry_date' => now()->addMonths(18)->toDateString(),
                'quantity_on_hand' => 450,
                'quantity_reserved' => 50,
                'quantity_available' => 400,
                'status' => 'available',
                'unit_cost' => 180000,
            ]
        );

        ScmInventoryStock::updateOrCreate(
            ['product_id' => $rmMadu->id, 'location_id' => $locRaw->id, 'batch_number' => 'LOT-MD-202610'],
            [
                'plant_id' => $plant->id,
                'expiry_date' => now()->addMonths(24)->toDateString(),
                'quantity_on_hand' => 800,
                'quantity_reserved' => 100,
                'quantity_available' => 700,
                'status' => 'available',
                'unit_cost' => 85000,
            ]
        );

        // Near-expiry stock alert
        ScmInventoryStock::updateOrCreate(
            ['product_id' => $rmMeniran->id, 'location_id' => $locRaw->id, 'batch_number' => 'LOT-MN-202501'],
            [
                'plant_id' => $plant->id,
                'expiry_date' => now()->addDays(45)->toDateString(),
                'quantity_on_hand' => 60,
                'quantity_reserved' => 0,
                'quantity_available' => 60,
                'status' => 'available',
                'unit_cost' => 220000,
            ]
        );

        // Packaging stock in WH-RAW
        ScmInventoryStock::updateOrCreate(
            ['product_id' => $pmSachet->id, 'location_id' => $locRaw->id, 'batch_number' => 'LOT-FL-202611'],
            [
                'plant_id' => $plant->id,
                'expiry_date' => now()->addYears(3)->toDateString(),
                'quantity_on_hand' => 120000,
                'quantity_reserved' => 25000,
                'quantity_available' => 95000,
                'status' => 'available',
                'unit_cost' => 450,
            ]
        );

        // Quarantine Stock in WH-QRN
        ScmInventoryStock::updateOrCreate(
            ['product_id' => $rmJahe->id, 'location_id' => $locQrn->id, 'batch_number' => 'LOT-JH-NEW01'],
            [
                'plant_id' => $plant->id,
                'expiry_date' => now()->addMonths(20)->toDateString(),
                'quantity_on_hand' => 150,
                'quantity_reserved' => 0,
                'quantity_available' => 0,
                'status' => 'quarantine',
                'unit_cost' => 180000,
            ]
        );

        // Stock Movement Ledger
        ScmStockMovement::firstOrCreate(
            ['movement_number' => 'MOV-202611-0001'],
            [
                'product_id' => $rmJahe->id,
                'from_location_id' => null,
                'to_location_id' => $locRaw->id,
                'reference_type' => 'goods_receipt',
                'quantity' => 450,
                'batch_number' => 'LOT-JH-202609',
                'notes' => 'Penerimaan Inbound PO-202609-001 Lulus QC',
                'created_by' => $user->id,
            ]
        );

        // 9. Goods Receipts & QC Inspections
        // Receipt 1: Completed & QC Passed
        $receipt1 = ScmGoodsReceipt::firstOrCreate(
            ['grn_number' => 'GR-202611-0001'],
            [
                'po_number' => 'PO-202611-0001',
                'vendor_id' => $vendor1->id,
                'plant_id' => $plant->id,
                'delivery_order_number' => 'SJ-SAH-9921',
                'receipt_date' => now()->subDays(3)->toDateString(),
                'received_by' => $user->id,
                'status' => 'completed',
                'notes' => 'Pengiriman ekstrak jahe merah kemasan drum tersegel rapat.',
            ]
        );

        $receiptItem1 = ScmGoodsReceiptItem::firstOrCreate(
            ['goods_receipt_id' => $receipt1->id, 'product_id' => $rmJahe->id],
            [
                'item_name' => $rmJahe->name,
                'received_qty' => 100,
                'passed_qty' => 100,
                'rejected_qty' => 0,
                'uom' => 'KG',
                'batch_number' => 'LOT-JH-9921',
                'expiry_date' => now()->addYears(2)->toDateString(),
                'unit_price' => 180000,
                'status' => 'passed',
            ]
        );

        ScmQcInspection::firstOrCreate(
            ['inspection_number' => 'QC-202611-0001'],
            [
                'goods_receipt_item_id' => $receiptItem1->id,
                'product_id' => $rmJahe->id,
                'batch_number' => 'LOT-JH-9921',
                'sample_qty' => 5,
                'passed_qty' => 100,
                'rejected_qty' => 0,
                'inspector_id' => $user->id,
                'inspection_date' => now()->subDays(2)->toDateString(),
                'is_visual_passed' => true,
                'is_lab_passed' => true,
                'is_quantity_passed' => true,
                'is_docs_passed' => true,
                'status' => 'passed',
                'notes' => 'Uji kadar air 6.2%, gingerol teridentifikasi positif, bebas aflatoksin.',
            ]
        );

        // Receipt 2: Pending Quarantine
        $receipt2 = ScmGoodsReceipt::firstOrCreate(
            ['grn_number' => 'GR-202611-0002'],
            [
                'po_number' => 'PO-202611-0002',
                'vendor_id' => $vendor2->id,
                'plant_id' => $plant->id,
                'delivery_order_number' => 'SJ-FPP-1044',
                'receipt_date' => now()->toDateString(),
                'received_by' => $user->id,
                'status' => 'quarantine',
                'notes' => 'Baru tiba pagi ini, menunggu analis QC sampling.',
            ]
        );

        $receiptItem2 = ScmGoodsReceiptItem::firstOrCreate(
            ['goods_receipt_id' => $receipt2->id, 'product_id' => $pmSachet->id],
            [
                'item_name' => $pmSachet->name,
                'received_qty' => 50000,
                'passed_qty' => 0,
                'rejected_qty' => 0,
                'uom' => 'PCS',
                'batch_number' => 'LOT-FL-1044',
                'expiry_date' => now()->addYears(3)->toDateString(),
                'unit_price' => 450,
                'status' => 'quarantine',
            ]
        );

        ScmQcInspection::firstOrCreate(
            ['inspection_number' => 'QC-202611-0002'],
            [
                'goods_receipt_item_id' => $receiptItem2->id,
                'product_id' => $pmSachet->id,
                'batch_number' => 'LOT-FL-1044',
                'sample_qty' => 500,
                'passed_qty' => 0,
                'rejected_qty' => 0,
                'inspector_id' => $user->id,
                'inspection_date' => now()->toDateString(),
                'is_visual_passed' => false,
                'is_lab_passed' => false,
                'is_quantity_passed' => false,
                'is_docs_passed' => false,
                'status' => 'pending',
                'notes' => 'Menunggu sampling mikrobiologi dan uji ketebalan foil.',
            ]
        );

        // 10. Material Usages (BSTB)
        $usage = ScmMaterialUsage::firstOrCreate(
            ['usage_number' => 'BSTB-202611-0001'],
            [
                'work_order_number' => 'SPK-202611-001',
                'plant_id' => $plant->id,
                'department_id' => $deptProd->id,
                'usage_date' => now()->subDay()->toDateString(),
                'issued_by' => $user->id,
                'received_by' => $user->id,
                'status' => 'issued',
                'notes' => 'Penyerahan bahan baku formulasi ekstrak jahe dan madu',
            ]
        );

        ScmMaterialUsageItem::firstOrCreate(
            ['material_usage_id' => $usage->id, 'product_id' => $rmJahe->id],
            [
                'batch_number' => 'LOT-JH-202609',
                'quantity' => 25,
                'uom' => 'KG',
                'unit_cost' => 180000,
                'notes' => 'Ruang mixing A',
            ]
        );

        ScmMaterialUsageItem::firstOrCreate(
            ['material_usage_id' => $usage->id, 'product_id' => $rmMadu->id],
            [
                'batch_number' => 'LOT-MD-202610',
                'quantity' => 50,
                'uom' => 'KG',
                'unit_cost' => 85000,
                'notes' => 'Ruang mixing A',
            ]
        );

        // 11. Material Return (Vendor Reject Memo)
        $return = ScmMaterialReturn::firstOrCreate(
            ['return_number' => 'RET-202611-0001'],
            [
                'goods_receipt_id' => $receipt1->id,
                'vendor_id' => $vendor1->id,
                'return_date' => now()->subDays(2)->toDateString(),
                'reason' => 'Deviasi kadar air melebihi batas 10% dan kemasan sobek.',
                'total_amount' => 3600000,
                'status' => 'approved',
                'created_by' => $user->id,
                'approved_by' => $user->id,
                'approved_at' => now()->subDays(2),
            ]
        );

        ScmMaterialReturnItem::firstOrCreate(
            ['material_return_id' => $return->id, 'product_id' => $rmJahe->id],
            [
                'batch_number' => 'LOT-JH-REJ-01',
                'quantity' => 20,
                'uom' => 'KG',
                'unit_price' => 180000,
                'subtotal' => 3600000,
                'reason' => 'Uji kadar air 12.8% (Maksimal 8.0%)',
            ]
        );

        // 12. 3-Way Matching AP Audit & Vendor Bills
        $matching = ScmThreeWayMatching::firstOrCreate(
            ['matching_number' => 'TWM-202611-0001'],
            [
                'po_number' => 'PO-202611-0001',
                'goods_receipt_id' => $receipt1->id,
                'vendor_id' => $vendor1->id,
                'vendor_invoice_number' => 'INV-SAH-2026-088',
                'po_total_qty' => 100,
                'po_unit_price' => 180000,
                'gr_passed_qty' => 100,
                'invoice_qty' => 100,
                'invoice_amount' => 18000000,
                'discrepancy_qty' => 0,
                'discrepancy_amount' => 0,
                'status' => 'approved',
                'notes' => 'Audit 3-way matching klop 100% tanpa selisih kuantitas atau harga.',
                'audited_by' => $user->id,
                'approved_by' => $user->id,
                'approved_at' => now()->subDay(),
            ]
        );

        ScmVendorBill::firstOrCreate(
            ['bill_number' => 'BILL-202611-0001'],
            [
                'three_way_matching_id' => $matching->id,
                'goods_receipt_id' => $receipt1->id,
                'vendor_id' => $vendor1->id,
                'invoice_number' => 'INV-SAH-2026-088',
                'bill_date' => now()->subDay()->toDateString(),
                'due_date' => now()->addDays(29)->toDateString(),
                'subtotal' => 18000000,
                'tax_amount' => 1980000,
                'total_amount' => 19980000,
                'status' => 'approved',
                'created_by' => $user->id,
                'notes' => 'Tagihan resmi PT Sumber Alam Ekstrak Herbal - Termin 30 Hari',
            ]
        );

        // 13. Simulated MRP Run
        $mrpRun = ScmMrpRun::firstOrCreate(
            ['run_number' => 'MRP-202611-0001'],
            [
                'schedule_id' => $mps1->id,
                'period_name' => 'November 2026',
                'parameters_json' => ['horizon_months' => 3, 'lead_time_days' => 14],
                'status' => 'simulated',
                'notes' => 'Ledakan kebutuhan material batch November 2026 - Januari 2027',
                'executed_by' => $user->id,
            ]
        );

        ScmMrpItem::firstOrCreate(
            ['mrp_run_id' => $mrpRun->id, 'product_id' => $rmMeniran->id],
            [
                'gross_requirement' => 200,
                'on_hand_stock' => 60,
                'allocated_stock' => 0,
                'outstanding_po_qty' => 0,
                'safety_stock' => 80,
                'net_requirement' => 220,
                'suggested_order_date' => now()->addDays(3)->toDateString(),
                'suggested_due_date' => now()->addDays(14)->toDateString(),
                'status' => 'pending',
                'vendor_id' => $vendor1->id,
            ]
        );

        ScmMrpItem::firstOrCreate(
            ['mrp_run_id' => $mrpRun->id, 'product_id' => $rmJahe->id],
            [
                'gross_requirement' => 300,
                'on_hand_stock' => 450,
                'allocated_stock' => 50,
                'outstanding_po_qty' => 100,
                'safety_stock' => 150,
                'net_requirement' => 0,
                'suggested_order_date' => null,
                'suggested_due_date' => null,
                'status' => 'ignored',
                'vendor_id' => $vendor1->id,
            ]
        );
    }
}
