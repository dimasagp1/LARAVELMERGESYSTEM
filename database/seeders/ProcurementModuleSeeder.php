<?php

namespace Database\Seeders;

use App\Core\Models\Department;
use App\Core\Models\Vendor;
use App\Models\User;
use App\Modules\Procurement\Models\DepartmentBudget;
use App\Modules\Procurement\Models\ItemStandardPrice;
use App\Modules\Procurement\Models\PrBon;
use App\Modules\Procurement\Models\PrItem;
use App\Modules\Procurement\Models\PrItemDelivery;
use App\Modules\Procurement\Models\PrItemDeliveryPlan;
use App\Modules\Procurement\Models\ProductionLossTimeLog;
use App\Modules\Procurement\Models\PurchaseRequest;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProcurementModuleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Permissions & Roles
        $permissions = [
            'procurement.view',
            'procurement.pr.create',
            'procurement.pr.estimate',
            'procurement.pr.approve_l1',
            'procurement.pr.approve_l2',
            'procurement.bon.create',
            'procurement.bon.approve',
            'procurement.po.process',
            'procurement.delivery.record',
            'procurement.budget.manage',
            'procurement.price.manage',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $roles = [
            'user' => ['procurement.view', 'procurement.pr.create'],
            'operational_manager' => ['procurement.view', 'procurement.pr.create', 'procurement.pr.approve_l1'],
            'manager_fat' => ['procurement.view', 'procurement.pr.approve_l1', 'procurement.budget.manage'],
            'general_manager' => ['procurement.view', 'procurement.pr.approve_l2', 'procurement.bon.approve', 'procurement.budget.manage'],
            'procurement' => ['procurement.view', 'procurement.pr.estimate', 'procurement.bon.create', 'procurement.po.process', 'procurement.delivery.record', 'procurement.price.manage'],
            'procurement_holding' => ['procurement.view', 'procurement.pr.estimate', 'procurement.delivery.record'],
            'superadmin' => $permissions,
        ];

        foreach ($roles as $roleName => $perms) {
            $r = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $r->syncPermissions($perms);
        }

        // 2. Demo Users
        $deptProc = Department::firstOrCreate(
            ['code' => 'PROC'],
            ['name' => 'Procurement & Purchasing', 'description' => 'Bagian Pengadaan']
        );
        $deptProd = Department::where('code', 'PROD')->first() ?? $deptProc;
        $deptFat = Department::where('code', 'FIN')->first() ?? $deptProc;

        $buyer = User::firstOrCreate(
            ['email' => 'buyer@herbatech.com'],
            [
                'name' => 'Rahmat Hidayat (Buyer)',
                'password' => Hash::make('password'),
                'department_id' => $deptProc->id,
                'status' => 'active',
            ]
        );
        $buyer->assignRole('procurement');

        $fatUser = User::firstOrCreate(
            ['email' => 'fat@herbatech.com'],
            [
                'name' => 'Siti Nurhaliza (FAT Manager)',
                'password' => Hash::make('password'),
                'department_id' => $deptFat->id,
                'status' => 'active',
            ]
        );
        $fatUser->assignRole('manager_fat');

        // Assign OM role to existing OM user if exists
        $om = User::where('email', 'om@herbatech.com')->first();
        if ($om) {
            $om->assignRole('operational_manager');
        }

        // Assign GM role to existing GM user if exists
        $gm = User::where('email', 'gm@herbatech.com')->first();
        if ($gm) {
            $gm->assignRole('general_manager');
        }

        // 3. Department Budgets (FAT)
        $year = 2026;
        $month = (int) Carbon::now()->format('m');
        $departments = Department::all();

        foreach ($departments as $d) {
            DepartmentBudget::firstOrCreate(
                [
                    'department_id' => $d->id,
                    'fiscal_year' => $year,
                    'month' => $month,
                    'purpose' => 'Biaya Operasional Rutin',
                ],
                [
                    'initial_budget' => 350000000.00,
                    'realized_amount' => 125000000.00,
                    'booked_amount' => 65000000.00,
                ]
            );
        }

        // 4. Standard Prices & AVL
        $vendorA = Vendor::firstOrCreate(
            ['code' => 'VND-001'],
            ['name' => 'PT Bahan Alam Makmur', 'category' => 'Raw Material', 'avl_rating' => 4.8, 'is_active' => true]
        );
        $vendorB = Vendor::firstOrCreate(
            ['code' => 'VND-002'],
            ['name' => 'PT Mitra Kemasan Sejahtera', 'category' => 'Packaging', 'avl_rating' => 4.6, 'is_active' => true]
        );

        $standardCatalog = [
            ['code' => 'RM-CUR-01', 'name' => 'Ekstrak Curcuma Xanthorrhiza (Temulawak)', 'cat' => 'material', 'uom' => 'kg', 'price' => 250000, 'vendor' => $vendorA->name],
            ['code' => 'RM-MEN-01', 'name' => 'Ekstrak Phyllanthus Niruri (Meniran)', 'cat' => 'material', 'uom' => 'kg', 'price' => 320000, 'vendor' => $vendorA->name],
            ['code' => 'PM-BOT-100', 'name' => 'Botol Kaca Amber 100ml Tebal', 'cat' => 'material', 'uom' => 'pcs', 'price' => 2800, 'vendor' => $vendorB->name],
            ['code' => 'PM-BOX-01', 'name' => 'Karton Box Master Gelombang (A1)', 'cat' => 'material', 'uom' => 'pcs', 'price' => 8500, 'vendor' => $vendorB->name],
            ['code' => 'SP-BLT-M8', 'name' => 'Baut Stainless Steel M8x30 Din 933', 'cat' => 'supporting', 'uom' => 'pcs', 'price' => 6500, 'vendor' => 'Toko Baut Jaya'],
            ['code' => 'SP-MSK-01', 'name' => 'Masker Medis 3-Ply Steril (Box 50s)', 'cat' => 'supporting', 'uom' => 'box', 'price' => 35000, 'vendor' => 'CV Medika Farma'],
        ];

        foreach ($standardCatalog as $sc) {
            ItemStandardPrice::firstOrCreate(
                ['item_code' => $sc['code'], 'effective_year' => $year],
                [
                    'item_name' => $sc['name'],
                    'category_type' => $sc['cat'],
                    'uom' => $sc['uom'],
                    'standard_price' => $sc['price'],
                    'vendor_name' => $sc['vendor'],
                    'is_active' => true,
                ]
            );
        }

        // 5. Purchase Requests & Granular Items
        // PR 1: Operational PR (Raw Materials in various granular stages)
        $pr1 = PurchaseRequest::firstOrCreate(
            ['pr_number' => 'PR-PROD-202611-0001'],
            [
                'user_id' => $buyer->id,
                'department_id' => $deptProd->id,
                'request_date' => Carbon::now()->subDays(5),
                'purpose' => 'Bahan Baku Rutin Produksi (Raw Material)',
                'pr_type' => 'operational',
                'status' => 'ordered',
                'total_amount' => 125000000,
                'bypass_budget_check' => false,
            ]
        );

        $item1A = PrItem::firstOrCreate(
            ['purchase_request_id' => $pr1->id, 'item_name' => 'Ekstrak Curcuma Xanthorrhiza (Temulawak)'],
            [
                'item_code' => 'RM-CUR-01',
                'quantity' => 300,
                'ready_quantity' => 150,
                'uom' => 'kg',
                'estimated_price' => 75000000,
                'actual_price' => 75000000,
                'vendor_id' => $vendorA->id,
                'vendor_name' => $vendorA->name,
                'po_number' => 'PO-202611-0012',
                'status' => 'ordered',
                'rekap_po_odoo' => true,
                'is_incoming' => true,
            ]
        );

        // Record Delivery plan & Partial delivery for item 1A
        PrItemDeliveryPlan::firstOrCreate(
            ['pr_item_id' => $item1A->id, 'plan_date' => Carbon::now()->subDays(2)->toDateString()],
            ['plan_quantity' => 150, 'is_active' => true, 'notes' => 'Batch 1 Pengiriman Cikarang']
        );
        PrItemDeliveryPlan::firstOrCreate(
            ['pr_item_id' => $item1A->id, 'plan_date' => Carbon::now()->addDays(5)->toDateString()],
            ['plan_quantity' => 150, 'is_active' => true, 'notes' => 'Batch 2 Sisa Pengiriman']
        );

        PrItemDelivery::firstOrCreate(
            ['pr_item_id' => $item1A->id, 'delivery_order_number' => 'SJ-BAM-9081'],
            [
                'delivery_date' => Carbon::now()->subDays(2)->toDateString(),
                'delivered_quantity' => 150,
                'received_by' => $buyer->id,
                'is_rejected' => false,
            ]
        );

        $item1B = PrItem::firstOrCreate(
            ['purchase_request_id' => $pr1->id, 'item_name' => 'Botol Kaca Amber 100ml Tebal'],
            [
                'item_code' => 'PM-BOT-100',
                'quantity' => 15000,
                'ready_quantity' => 0,
                'uom' => 'pcs',
                'estimated_price' => 42000000,
                'actual_price' => 40500000,
                'vendor_id' => $vendorB->id,
                'vendor_name' => $vendorB->name,
                'status' => 'waiting_gm',
                'rekap_po_odoo' => true,
            ]
        );

        // PR 2: Special Procurement (Jalur Bon Sementara)
        $deptRnd = Department::where('code', 'RND')->first() ?? $deptProc;
        $pr2 = PurchaseRequest::firstOrCreate(
            ['pr_number' => 'PR-RND-202611-0002'],
            [
                'user_id' => $buyer->id,
                'department_id' => $deptRnd->id,
                'request_date' => Carbon::now()->subDays(3),
                'purpose' => 'Pengadaan Segera Toko Offline / Kas Bon',
                'pr_type' => 'operational',
                'status' => 'pending',
                'total_amount' => 8500000,
                'bypass_budget_check' => false,
            ]
        );

        $itemBon1 = PrItem::firstOrCreate(
            ['purchase_request_id' => $pr2->id, 'item_name' => 'Baut Stainless Steel M8x30 Din 933'],
            [
                'item_code' => 'SP-BLT-M8',
                'quantity' => 500,
                'ready_quantity' => 0,
                'uom' => 'pcs',
                'estimated_price' => 3250000,
                'actual_price' => 3250000,
                'is_bon' => true,
                'bon_unit_price' => 3250000,
                'status' => 'waiting_gm_bon',
            ]
        );

        $itemBon2 = PrItem::firstOrCreate(
            ['purchase_request_id' => $pr2->id, 'item_name' => 'Pelumas Sintetik Mesin Rotary Lab (4 Liter)'],
            [
                'item_code' => 'SP-OIL-01',
                'quantity' => 2,
                'ready_quantity' => 0,
                'uom' => 'galon',
                'estimated_price' => 1800000,
                'actual_price' => 1800000,
                'is_bon' => true,
                'bon_unit_price' => 1800000,
                'status' => 'waiting_gm_bon',
            ]
        );

        // Bon Dossier awaiting GM
        $bon = PrBon::firstOrCreate(
            ['bon_number' => 'BON-202611-0001'],
            [
                'purchase_request_id' => $pr2->id,
                'vendor_name' => 'Toko Baut Jaya & Pelumas Cikarang',
                'bon_date' => Carbon::now()->toDateString(),
                'total_amount' => 5050000,
                'status' => 'waiting_gm',
                'created_by' => $buyer->id,
            ]
        );
        $itemBon1->update(['pr_bon_id' => $bon->id]);
        $itemBon2->update(['pr_bon_id' => $bon->id]);

        // PR 3: Item needing L1 OM approval
        $pr3 = PurchaseRequest::firstOrCreate(
            ['pr_number' => 'PR-RND-202611-0003'],
            [
                'user_id' => $buyer->id,
                'department_id' => $deptRnd->id,
                'request_date' => Carbon::now()->subDay(),
                'purpose' => 'Reagen Uji Stabilitas Lab',
                'pr_type' => 'operational',
                'status' => 'pending',
                'total_amount' => 14500000,
                'bypass_budget_check' => false,
            ]
        );

        PrItem::firstOrCreate(
            ['purchase_request_id' => $pr3->id, 'item_name' => 'Reagen Acetonitrile HPLC Grade (4x2.5L)'],
            [
                'quantity' => 1,
                'ready_quantity' => 0,
                'uom' => 'box',
                'estimated_price' => 14500000,
                'status' => 'pending', // Waiting L1 OM approval
                'rekap_po_odoo' => true,
            ]
        );

        // PR 4: PR with rejected item (demonstrating bulk revision feature)
        $pr4 = PurchaseRequest::firstOrCreate(
            ['pr_number' => 'PR-GA-202611-0004'],
            [
                'user_id' => $buyer->id,
                'department_id' => $deptProc->id,
                'request_date' => Carbon::now()->subDays(4),
                'purpose' => 'Perlengkapan Umum dan ATK',
                'pr_type' => 'non_operational',
                'status' => 'pending',
                'total_amount' => 7500000,
            ]
        );

        PrItem::firstOrCreate(
            ['purchase_request_id' => $pr4->id, 'item_name' => 'Kursi Ergonomis Ruang Rapat (Ditolak)'],
            [
                'quantity' => 5,
                'uom' => 'unit',
                'estimated_price' => 7500000,
                'status' => 'rejected_om',
                'rejection_reason' => 'Harga terlalu tinggi, mohon ajukan vendor alternatif yang ekonomis',
            ]
        );

        // 6. Production Loss Time Logs (Sasaran Mutu 1)
        ProductionLossTimeLog::firstOrCreate(
            ['log_date' => Carbon::now()->subDays(10)->toDateString(), 'machine_name' => 'Line Granulasi 1'],
            [
                'downtime_hours' => 1.5,
                'material_name' => 'Ekstrak Temulawak',
                'output_per_hour' => 450,
                'product_unit_value' => 25000,
                'loss_value' => 16875000,
                'target_revenue' => 5000000000,
                'created_by' => $buyer->id,
            ]
        );
    }
}
