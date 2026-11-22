<?php

namespace Database\Seeders;

use App\Core\Models\Department;
use App\Core\Models\Location;
use App\Core\Models\Plant;
use App\Core\Models\Uom;
use App\Models\User;
use App\Modules\GeneralAffair\Models\AssetCategory;
use App\Modules\GeneralAffair\Models\AtkCategory;
use App\Modules\GeneralAffair\Models\AtkItem;
use App\Modules\GeneralAffair\Models\AtkRequest;
use App\Modules\GeneralAffair\Models\AtkRequestItem;
use App\Modules\GeneralAffair\Models\CleaningCheckpoint;
use App\Modules\GeneralAffair\Models\CleaningCheckpointLog;
use App\Modules\GeneralAffair\Models\FpuMaterial;
use App\Modules\GeneralAffair\Models\FpuRequest;
use App\Modules\GeneralAffair\Models\GaAsset;
use App\Modules\GeneralAffair\Models\SecurityCheckpoint;
use App\Modules\GeneralAffair\Models\SecurityCheckpointLog;
use App\Modules\GeneralAffair\Models\WoHandover;
use App\Modules\GeneralAffair\Models\WorkOrder;
use App\Modules\GeneralAffair\Models\WorkOrderProgress;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class GeneralAffairModuleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@herbatech.com')->first() ?? User::first();
        $plants = Plant::all();
        $plant1 = $plants->first();
        $plant2 = $plants->skip(1)->first() ?? $plant1;
        $departments = Department::all();
        $deptGa = $departments->where('code', 'GA')->first() ?? $departments->first();
        $deptProd = $departments->where('code', 'PROD')->first() ?? $departments->first();
        $deptQa = $departments->where('code', 'QA')->first() ?? $departments->first();
        $locations = Location::all();
        $loc1 = $locations->first();
        $loc2 = $locations->skip(1)->first() ?? $loc1;
        $uomPcs = Uom::where('code', 'PCS')->first() ?? Uom::first();
        $uomRoll = Uom::where('code', 'ROLL')->first() ?? $uomPcs;
        $uomBox = Uom::where('code', 'BOX')->first() ?? $uomPcs;

        // 1. Asset Categories
        $catIT = AssetCategory::updateOrCreate(
            ['code' => '01'],
            ['name' => 'Elektronik & IT', 'useful_life_years' => 4]
        );
        $catKendaraan = AssetCategory::updateOrCreate(
            ['code' => '02'],
            ['name' => 'Kendaraan Operasional', 'useful_life_years' => 8]
        );
        $catMesin = AssetCategory::updateOrCreate(
            ['code' => '03'],
            ['name' => 'Mesin & Utilitas Fasilitas', 'useful_life_years' => 8]
        );
        $catFurnitur = AssetCategory::updateOrCreate(
            ['code' => '04'],
            ['name' => 'Furnitur & Peralatan Kantor', 'useful_life_years' => 4]
        );

        // 2. ATK & RT Categories
        $catAtk = AtkCategory::updateOrCreate(
            ['name' => 'Alat Tulis Kantor (ATK)'],
            ['type' => 'ATK']
        );
        $catRt = AtkCategory::updateOrCreate(
            ['name' => 'Rumah Tangga & Kebersihan (RT)'],
            ['type' => 'RT']
        );

        // 3. ATK & RT Items
        $itemKertas = AtkItem::updateOrCreate(
            ['item_code' => 'ATK-KRT-01'],
            [
                'category_id' => $catAtk->id,
                'name' => 'Kertas HVS PaperOne A4 80 GSM',
                'uom_id' => $uomBox?->id,
                'min_stock' => 20,
                'current_stock' => 150,
                'standard_cost' => 245000,
                'is_active' => true,
            ]
        );
        $itemPulpen = AtkItem::updateOrCreate(
            ['item_code' => 'ATK-PLP-01'],
            [
                'category_id' => $catAtk->id,
                'name' => 'Pulpen Pilot G2 0.5mm Hitam',
                'uom_id' => $uomPcs?->id,
                'min_stock' => 50,
                'current_stock' => 300,
                'standard_cost' => 18500,
                'is_active' => true,
            ]
        );
        $itemSabun = AtkItem::updateOrCreate(
            ['item_code' => 'RT-SBN-01'],
            [
                'category_id' => $catRt->id,
                'name' => 'Sabun Cuci Tangan Antibakterial 5 Liter',
                'uom_id' => $uomPcs?->id,
                'min_stock' => 10,
                'current_stock' => 45,
                'standard_cost' => 85000,
                'is_active' => true,
            ]
        );
        $itemTisu = AtkItem::updateOrCreate(
            ['item_code' => 'RT-TSU-01'],
            [
                'category_id' => $catRt->id,
                'name' => 'Tisu Gulung Toilet Kimberly-Clark (Isi 12)',
                'uom_id' => $uomRoll?->id,
                'min_stock' => 15,
                'current_stock' => 80,
                'standard_cost' => 65000,
                'is_active' => true,
            ]
        );

        // 4. Fixed Assets (Multi-Plant)
        $asset1 = GaAsset::updateOrCreate(
            ['asset_code' => 'HII-01-GA-03-CHL-0001'],
            [
                'name' => 'Chiller Air-Cooled Trane 50 TR Plant 1',
                'plant_id' => $plant1?->id,
                'department_id' => $deptGa?->id,
                'location_id' => $loc1?->id,
                'category_id' => $catMesin->id,
                'classification' => 'UTAMA',
                'purchase_date' => Carbon::now()->subMonths(18)->toDateString(),
                'purchase_cost' => 450000000,
                'useful_life_years' => 8,
                'condition' => 'Baik',
                'pic_name' => 'Budi Santoso (Supervisor Maintenance)',
                'barcode_qr' => 'HII-01-GA-03-CHL-0001',
                'notes' => 'Servis berkala setiap 3 bulan oleh vendor Trane Indonesia.',
            ]
        );

        $asset2 = GaAsset::updateOrCreate(
            ['asset_code' => 'HII-01-PRO-03-HVC-0001'],
            [
                'name' => 'HVAC Cleanroom Ruang Ekstraksi Herbal Plant 1',
                'plant_id' => $plant1?->id,
                'department_id' => $deptProd?->id,
                'location_id' => $loc2?->id,
                'category_id' => $catMesin->id,
                'classification' => 'UTAMA',
                'purchase_date' => Carbon::now()->subMonths(12)->toDateString(),
                'purchase_cost' => 320000000,
                'useful_life_years' => 8,
                'condition' => 'Baik',
                'pic_name' => 'Agus Priyono (Teknisi HVAC)',
                'barcode_qr' => 'HII-01-PRO-03-HVC-0001',
                'notes' => 'Filter HEPA efisiensi 99.97% diganti per 6 bulan.',
            ]
        );

        $asset3 = GaAsset::updateOrCreate(
            ['asset_code' => 'HII-02-GA-03-GEN-0001'],
            [
                'name' => 'Genset Silent Cummins 500 kVA Plant 2',
                'plant_id' => $plant2?->id,
                'department_id' => $deptGa?->id,
                'location_id' => $loc1?->id,
                'category_id' => $catMesin->id,
                'classification' => 'UTAMA',
                'purchase_date' => Carbon::now()->subMonths(24)->toDateString(),
                'purchase_cost' => 680000000,
                'useful_life_years' => 8,
                'condition' => 'Baik',
                'pic_name' => 'Dwi Cahyono (Koordinator GA Boyolali)',
                'barcode_qr' => 'HII-02-GA-03-GEN-0001',
                'notes' => 'Kapasitas tangki solar 1.500 liter cadangan PLN.',
            ]
        );

        // 5. Patrol Checkpoints (Security & Cleaning)
        $secCp1 = SecurityCheckpoint::updateOrCreate(
            ['code' => 'SEC-P1-01'],
            [
                'name' => 'Pos Satpam Gerbang Utama (Gate 1)',
                'location_description' => 'Akses keluar masuk kendaraan dan tamu Sukoharjo',
                'plant_id' => $plant1?->id,
                'target_duration_minutes' => 15,
                'qr_token' => 'SEC-GATE1-SKH-01',
                'is_active' => true,
            ]
        );
        $secCp2 = SecurityCheckpoint::updateOrCreate(
            ['code' => 'SEC-P1-02'],
            [
                'name' => 'Ruang Chiller & Genset Utilitas',
                'location_description' => 'Area terlarang perimeter selatan pabrik',
                'plant_id' => $plant1?->id,
                'target_duration_minutes' => 20,
                'qr_token' => 'SEC-UTILITAS-SKH-02',
                'is_active' => true,
            ]
        );
        $secCp3 = SecurityCheckpoint::updateOrCreate(
            ['code' => 'SEC-P2-01'],
            [
                'name' => 'Pos Gerbang Plant 2 Boyolali',
                'location_description' => 'Pintu masuk gudang bahan baku Boyolali',
                'plant_id' => $plant2?->id,
                'target_duration_minutes' => 15,
                'qr_token' => 'SEC-GATE-BYL-01',
                'is_active' => true,
            ]
        );

        $cleanCp1 = CleaningCheckpoint::updateOrCreate(
            ['code' => 'CLN-P1-01'],
            [
                'name' => 'Toilet Kantor Manajemen Lantai 1',
                'plant_id' => $plant1?->id,
                'qr_token' => 'CLN-TOILET-LT1-01',
                'is_active' => true,
            ]
        );
        $cleanCp2 = CleaningCheckpoint::updateOrCreate(
            ['code' => 'CLN-P1-02'],
            [
                'name' => 'Ruang Ganti (Locker Room) Produksi',
                'plant_id' => $plant1?->id,
                'qr_token' => 'CLN-LOCKER-PROD-02',
                'is_active' => true,
            ]
        );

        // 6. Security & Cleaning Logs
        SecurityCheckpointLog::create([
            'checkpoint_id' => $secCp1->id,
            'user_id' => $admin->id,
            'scanned_at' => Carbon::now()->subHours(2),
            'duration_seconds' => 780,
            'status' => 'Aman',
            'is_anomaly' => false,
            'anomaly_notes' => null,
        ]);
        SecurityCheckpointLog::create([
            'checkpoint_id' => $secCp2->id,
            'user_id' => $admin->id,
            'scanned_at' => Carbon::now()->subMinutes(45),
            'duration_seconds' => 1100,
            'status' => 'Aman',
            'is_anomaly' => false,
            'anomaly_notes' => null,
        ]);

        CleaningCheckpointLog::create([
            'checkpoint_id' => $cleanCp1->id,
            'user_id' => $admin->id,
            'scanned_at' => Carbon::now()->subHours(3),
            'is_sanitized' => true,
            'checklist_json' => [
                'lantai' => true,
                'tempat_sampah' => true,
                'aroma' => true,
                'refill_sabun' => true,
            ],
            'notes' => 'Sanitasi pagi selesai, sabun dan tisu terisi penuh.',
        ]);

        // 7. Work Orders (Form A to D)
        // WO 1: Form A + B + C (In Progress)
        $wo1 = WorkOrder::updateOrCreate(
            ['wo_number' => 'WO-202611-0001'],
            [
                'user_id' => $admin->id,
                'department_id' => $deptProd?->id,
                'location_id' => $loc1?->id,
                'complaint_description' => 'Tekanan air pendingin pada chiller pipa sekunder menurun drastis dan terdapat rembesan di sambungan flange pipa.',
                'priority' => 'Tinggi',
                'category' => 'Gedung & Sipil',
                'status' => 'Sedang Dikerjakan',
                'assigned_engineer_id' => $admin->id,
                'assigned_at' => Carbon::now()->subDays(2),
                'check_result' => 'Gasket karet flange pipa 3 inch mengalami aus dan kebocoran mikro pada sambungan sambung.',
                'engineer_needs' => 'Penggantian 2 set gasket spiral wound 3 inch dan pengencangan baut flange.',
                'needs_material' => true,
            ]
        );

        $fpu1 = FpuRequest::updateOrCreate(
            ['work_order_id' => $wo1->id],
            [
                'fpu_number' => 'FPU-202611-0001',
                'request_date' => Carbon::today()->toDateString(),
                'status' => 'APPROVED',
                'verified_by_hrga' => true,
                'verified_by_user_id' => $admin->id,
                'verified_at' => Carbon::now()->subDay(),
                'known_by_manager' => true,
                'manager_user_id' => $admin->id,
                'manager_approved_at' => Carbon::now()->subDay(),
            ]
        );

        FpuMaterial::updateOrCreate(
            ['fpu_request_id' => $fpu1->id, 'material_name' => 'Gasket Spiral Wound 3 Inch ANSI 150'],
            [
                'specification' => 'ANSI 150 3 Inch',
                'quantity' => 2,
                'uom' => 'Pcs',
                'estimated_cost' => 850000,
            ]
        );
        FpuMaterial::updateOrCreate(
            ['fpu_request_id' => $fpu1->id, 'material_name' => 'Baut Stud Bolt High Tensile M16x80'],
            [
                'specification' => 'Grade 8.8 M16x80',
                'quantity' => 16,
                'uom' => 'Pcs',
                'estimated_cost' => 1000000,
            ]
        );

        WorkOrderProgress::create([
            'work_order_id' => $wo1->id,
            'user_id' => $admin->id,
            'progress_percent' => 70,
            'description' => 'Pipa telah dikuras, gasket lama dibersihkan dan siap dipasang komponen baru.',
        ]);

        // WO 2: Completed with BAST Form D
        $wo2 = WorkOrder::updateOrCreate(
            ['wo_number' => 'WO-202611-0002'],
            [
                'user_id' => $admin->id,
                'department_id' => $deptGa?->id,
                'location_id' => $loc2?->id,
                'complaint_description' => 'Lampu LED sorot area loading bay gudang bahan baku redup dan berkedip.',
                'priority' => 'Normal',
                'category' => 'Kelistrikan & AC',
                'status' => 'Selesai',
                'assigned_engineer_id' => $admin->id,
                'assigned_at' => Carbon::now()->subDays(4),
                'check_result' => 'Driver LED terbakar akibat lonjakan tegangan petir.',
                'engineer_needs' => 'Mengganti unit driver LED Philips 100 Watt.',
                'needs_material' => false,
            ]
        );

        WoHandover::updateOrCreate(
            ['work_order_id' => $wo2->id],
            [
                'bast_number' => 'BAST-202611-0001',
                'handover_date' => Carbon::now()->subDays(1)->toDateString(),
                'prepared_by_user_id' => $admin->id,
                'client_user_id' => $admin->id,
                'satisfaction_rating' => 5,
                'client_feedback' => 'Pekerjaan cepat dan penerangan dermaga kembali normal dan terang.',
                'is_confirmed' => true,
                'confirmed_at' => Carbon::now()->subDays(1),
            ]
        );

        // 8. ATK Requests
        $atk1 = AtkRequest::updateOrCreate(
            ['request_number' => 'ATK-202611-0001'],
            [
                'type' => 'ATK',
                'user_id' => $admin->id,
                'department_id' => $deptProd?->id,
                'tanggal_pengajuan' => Carbon::now()->subDays(1)->toDateString(),
                'alasan_pengajuan' => 'Kebutuhan cetak batch record produksi herbal dan formulir rekaman bets mingguan.',
                'status' => 'PENDING_ADMIN_GA',
                'manager_approved_by' => $admin->id,
                'manager_approved_at' => Carbon::now()->subHours(12),
                'manager_notes' => 'Disetujui sesuai kuota bulanan departemen produksi.',
            ]
        );

        AtkRequestItem::updateOrCreate(
            ['request_id' => $atk1->id, 'atk_item_id' => $itemKertas->id],
            [
                'quantity' => 10,
                'approved_quantity' => 10,
                'uom_id' => $uomBox?->id,
                'status' => 'APPROVED',
            ]
        );
        AtkRequestItem::updateOrCreate(
            ['request_id' => $atk1->id, 'atk_item_id' => $itemPulpen->id],
            [
                'quantity' => 20,
                'approved_quantity' => 15,
                'uom_id' => $uomPcs?->id,
                'status' => 'APPROVED',
                'rejection_note' => 'Disesuaikan dengan sisa stok departemen',
            ]
        );

        $atk2 = AtkRequest::updateOrCreate(
            ['request_number' => 'RT-202611-0001'],
            [
                'type' => 'RT',
                'user_id' => $admin->id,
                'department_id' => $deptGa?->id,
                'tanggal_pengajuan' => Carbon::now()->toDateString(),
                'alasan_pengajuan' => 'Pengisian stok sanitasi dispenser toilet pabrik Sukoharjo.',
                'status' => 'PENDING_MANAGER',
            ]
        );

        AtkRequestItem::updateOrCreate(
            ['request_id' => $atk2->id, 'atk_item_id' => $itemSabun->id],
            [
                'quantity' => 4,
                'approved_quantity' => 4,
                'uom_id' => $uomPcs?->id,
                'status' => 'PENDING',
            ]
        );
        AtkRequestItem::updateOrCreate(
            ['request_id' => $atk2->id, 'atk_item_id' => $itemTisu->id],
            [
                'quantity' => 10,
                'approved_quantity' => 10,
                'uom_id' => $uomRoll?->id,
                'status' => 'PENDING',
            ]
        );
    }
}
