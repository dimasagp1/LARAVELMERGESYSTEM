<?php

namespace Database\Seeders;

use App\Core\Models\Plant;
use App\Core\Models\Product;
use App\Core\Models\Uom;
use App\Models\User;
use App\Modules\Production\Models\DowntimeDetail;
use App\Modules\Production\Models\LaporanHarian;
use App\Modules\Production\Models\ProductionDowntimeReason;
use App\Modules\Production\Models\ProductionLine;
use App\Modules\Production\Models\ProductionMachine;
use App\Modules\Production\Models\RejectDetail;
use App\Modules\Production\Models\WeeklyPlan;
use Illuminate\Database\Seeder;

class ProductionModuleSeeder extends Seeder
{
    /**
     * Run the production module seeder.
     */
    public function run(): void
    {
        $plant = Plant::first() ?? Plant::create([
            'code' => 'PLT-SKH-01',
            'name' => 'Sukoharjo Plant 1 (Ekstrak & Formulasi)',
            'address' => 'Jl. Industri Farmasi No. 88 Sukoharjo',
            'is_active' => true,
        ]);

        $user = User::first() ?? User::create([
            'name' => 'Admin Manufaktur',
            'email' => 'admin@herbatech.com',
            'password' => bcrypt('password123'),
        ]);

        $spv = User::where('email', 'spv@herbatech.com')->first() ?? $user;

        // 1. Seed Master Production Lines
        $linesData = [
            [
                'line_number' => 'LINE-01',
                'name' => 'Lini Ekstraksi & Sirup Herbal',
                'plant_id' => $plant->id,
                'design_capacity_hourly' => 1500,
                'is_active' => true,
                'notes' => 'Lini cleanroom kelas D untuk pengolahan liquid & ekstraksi rimpang.',
            ],
            [
                'line_number' => 'LINE-02',
                'name' => 'Lini Kapsul & Tablet Herbal',
                'plant_id' => $plant->id,
                'design_capacity_hourly' => 2000,
                'is_active' => true,
                'notes' => 'Lini pengolahan solid, pencampuran serbuk kering & blister.',
            ],
            [
                'line_number' => 'LINE-03',
                'name' => 'Lini Sachet & Serbuk Seduh',
                'plant_id' => $plant->id,
                'design_capacity_hourly' => 1200,
                'is_active' => true,
                'notes' => 'Lini kemasan sachet aluminium foil multi-line packaging.',
            ],
        ];

        $lines = [];
        foreach ($linesData as $ld) {
            $lines[] = ProductionLine::updateOrCreate(['line_number' => $ld['line_number']], $ld);
        }

        // 2. Seed Master Machines
        $machinesData = [
            [
                'line_id' => $lines[0]->id,
                'machine_code' => 'MSN-EXT-01',
                'name' => 'Mesin Ekstraktor Vakum Suhu Rendah 1000L',
                'cycle_time_seconds' => 1.50,
                'brand' => 'HerbaTech Engineering',
                'model_number' => 'EVX-1000',
                'is_active' => true,
            ],
            [
                'line_id' => $lines[0]->id,
                'machine_code' => 'MSN-FL-01',
                'name' => 'Rotary Automatic Liquid Filling 8-Nozzle',
                'cycle_time_seconds' => 1.20,
                'brand' => 'PharmaFill Corp',
                'model_number' => 'PFL-800',
                'is_active' => true,
            ],
            [
                'line_id' => $lines[0]->id,
                'machine_code' => 'MSN-PK-01',
                'name' => 'Automatic Cartoner & Shrink Wrapper Sekunder',
                'cycle_time_seconds' => 1.80,
                'brand' => 'PackMaster',
                'model_number' => 'PM-350',
                'is_active' => true,
            ],
            [
                'line_id' => $lines[1]->id,
                'machine_code' => 'MSN-ENC-02',
                'name' => 'Mesin Enkapsulasi Semi-Otomatis 3000 Kapsul',
                'cycle_time_seconds' => 0.90,
                'brand' => 'CapTech',
                'model_number' => 'ENC-300',
                'is_active' => true,
            ],
        ];

        $machines = [];
        foreach ($machinesData as $md) {
            $machines[] = ProductionMachine::updateOrCreate(['machine_code' => $md['machine_code']], $md);
        }

        // 3. Seed Downtime Reasons
        $reasonsData = [
            ['code' => 'DT-MCH-01', 'category' => 'mesin', 'name' => 'Penyesuaian Suhu Chiller Ekstraktor', 'default_duration_minutes' => 30.0, 'requires_maintenance' => true],
            ['code' => 'DT-MCH-02', 'category' => 'mesin', 'name' => 'Pembersihan Nozzle & Sensor Filling', 'default_duration_minutes' => 15.0, 'requires_maintenance' => false],
            ['code' => 'DT-RM-01', 'category' => 'bahan', 'name' => 'Menunggu Rilis Bahan Kemas Botol Primer', 'default_duration_minutes' => 25.0, 'requires_maintenance' => false],
            ['code' => 'DT-VND-01', 'category' => 'vendor', 'name' => 'Masalah Dimensi Tutup Botol Cacat Vendor', 'default_duration_minutes' => 45.0, 'requires_maintenance' => false],
            ['code' => 'DT-OPS-01', 'category' => 'operasional', 'name' => 'Briefing Shift & Line Clearance CPOB', 'default_duration_minutes' => 15.0, 'requires_maintenance' => false],
            ['code' => 'DT-OPS-02', 'category' => 'operasional', 'name' => 'Istirahat Makan & Sholat Berjamaah', 'default_duration_minutes' => 60.0, 'requires_maintenance' => false],
        ];

        $reasons = [];
        foreach ($reasonsData as $rd) {
            $reasons[] = ProductionDowntimeReason::updateOrCreate(['code' => $rd['code']], $rd);
        }

        // Get Products
        $products = Product::take(4)->get();
        if ($products->isEmpty()) {
            $uom = Uom::first();
            $products = collect([
                Product::create([
                    'sku' => 'PRD-EXT-01',
                    'name' => 'Sirup Ekstrak Temulawak Plus 100ml',
                    'type' => 'fg',
                    'uom_id' => $uom?->id ?? 1,
                    'is_active' => true,
                ]),
                Product::create([
                    'sku' => 'PRD-KAP-02',
                    'name' => 'Kapsul Ekstrak Meniran & Kunyit 60s',
                    'type' => 'fg',
                    'uom_id' => $uom?->id ?? 1,
                    'is_active' => true,
                ]),
            ]);
        }

        // 4. Seed Weekly Plans (WPS) with Estafet Chain
        $wpsData = [
            // Batch 1: Sirup Temulawak (Mixing -> Filling -> Packing)
            [
                'plan_number' => 'WPS-202611-0001',
                'produk_id' => $products[0]->id,
                'line_id' => $lines[0]->id,
                'proses' => 'mixing',
                'batch_number' => 'EXT-2026-088',
                'odoo_mo_id' => 1011,
                'mo_status' => 'confirmed',
                'target_output' => 20000,
                'mp_count' => 10,
                'multiplier' => 2000,
                'packing_hold' => false,
                'tanggal' => now()->toDateString(),
                'shift' => 'shift1',
                'status' => 'selesai',
                'created_by' => $user->id,
            ],
            [
                'plan_number' => 'WPS-202611-0002',
                'produk_id' => $products[0]->id,
                'line_id' => $lines[0]->id,
                'proses' => 'filling',
                'batch_number' => 'EXT-2026-088',
                'odoo_mo_id' => 1011,
                'mo_status' => 'confirmed',
                'target_output' => 20000,
                'mp_count' => 10,
                'multiplier' => 2000,
                'packing_hold' => false,
                'tanggal' => now()->toDateString(),
                'shift' => 'shift1',
                'status' => 'aktif',
                'created_by' => $user->id,
            ],
            [
                'plan_number' => 'WPS-202611-0003',
                'produk_id' => $products[0]->id,
                'line_id' => $lines[0]->id,
                'proses' => 'packing',
                'batch_number' => 'EXT-2026-088',
                'odoo_mo_id' => 1011,
                'mo_status' => 'confirmed',
                'target_output' => 20000,
                'mp_count' => 10,
                'multiplier' => 2000,
                'packing_hold' => false,
                'tanggal' => now()->toDateString(),
                'shift' => 'shift2',
                'status' => 'draft',
                'created_by' => $user->id,
            ],
            // Batch 2: Kapsul Meniran (Mixing with Packing Hold)
            [
                'plan_number' => 'WPS-202611-0004',
                'produk_id' => $products[1]->id,
                'line_id' => $lines[1]->id,
                'proses' => 'mixing',
                'batch_number' => 'KAP-2026-042',
                'odoo_mo_id' => 1012,
                'mo_status' => 'confirmed',
                'target_output' => 15000,
                'mp_count' => 8,
                'multiplier' => 2000,
                'packing_hold' => true, // Menunggu botol sekunder
                'tanggal' => now()->toDateString(),
                'shift' => 'shift1',
                'status' => 'aktif',
                'created_by' => $user->id,
            ],
        ];

        $wpsPlans = [];
        foreach ($wpsData as $wp) {
            $wpsPlans[] = WeeklyPlan::updateOrCreate(['plan_number' => $wp['plan_number']], $wp);
        }

        // 5. Seed Laporan Harian Produksi (LHP) & Real Metrics
        $lhpReports = [
            // Selesai Mixing Batch 1
            [
                'report_number' => 'LHP-202611-0001',
                'weekly_plan_id' => $wpsPlans[0]->id,
                'produk_id' => $products[0]->id,
                'line_id' => $lines[0]->id,
                'mesin_id' => $machines[0]->id,
                'user_id' => $user->id,
                'tanggal' => now()->toDateString(),
                'shift' => 'shift1',
                'batch_number' => 'EXT-2026-088',
                'proses' => 'mixing',
                'ct' => 1.50,
                'start_time' => '07:30:00',
                'end_time' => '15:30:00',
                'gross_time_menit' => 480,
                'waktu_bersih_menit' => 435,
                'total_pause_menit' => 45,
                'timer_status' => 'end',
                'capacity_fisik' => 20500,
                'output_fisik' => 19800,
                'total_reject' => 200,
                'availability_persen' => 90.63,
                'performance_persen' => 93.33,
                'yield_persen' => 99.00,
                'oee_persen' => 83.74,
                'produktivitas_persen' => 99.00,
                'status' => 'locked',
                'locked_by' => $spv->id,
                'locked_at' => now()->subHours(2),
                'notes' => 'Ekstraksi berjalan lancar, aroma dan viskositas sesuai spesifikasi CPOB.',
            ],
            // Sedang Berjalan Filling Batch 1
            [
                'report_number' => 'LHP-202611-0002',
                'weekly_plan_id' => $wpsPlans[1]->id,
                'produk_id' => $products[0]->id,
                'line_id' => $lines[0]->id,
                'mesin_id' => $machines[1]->id,
                'user_id' => $user->id,
                'tanggal' => now()->toDateString(),
                'shift' => 'shift1',
                'batch_number' => 'EXT-2026-088',
                'proses' => 'filling',
                'ct' => 1.20,
                'start_time' => '08:00:00',
                'end_time' => null,
                'gross_time_menit' => 240,
                'waktu_bersih_menit' => 220,
                'total_pause_menit' => 20,
                'timer_status' => 'start',
                'capacity_fisik' => 12000,
                'output_fisik' => 10500,
                'total_reject' => 120,
                'availability_persen' => 91.67,
                'performance_persen' => 95.45,
                'yield_persen' => 98.87,
                'oee_persen' => 86.51,
                'produktivitas_persen' => 105.00,
                'status' => 'submitted',
                'notes' => 'Kecepatan pengisian stabil 45 botol/menit.',
            ],
            // Mixing Batch 2 (Kapsul)
            [
                'report_number' => 'LHP-202611-0003',
                'weekly_plan_id' => $wpsPlans[3]->id,
                'produk_id' => $products[1]->id,
                'line_id' => $lines[1]->id,
                'mesin_id' => $machines[3]->id,
                'user_id' => $user->id,
                'tanggal' => now()->toDateString(),
                'shift' => 'shift1',
                'batch_number' => 'KAP-2026-042',
                'proses' => 'mixing',
                'ct' => 0.90,
                'start_time' => '08:15:00',
                'end_time' => '14:45:00',
                'gross_time_menit' => 390,
                'waktu_bersih_menit' => 360,
                'total_pause_menit' => 30,
                'timer_status' => 'end',
                'capacity_fisik' => 15500,
                'output_fisik' => 14850,
                'total_reject' => 150,
                'availability_persen' => 92.31,
                'performance_persen' => 96.00,
                'yield_persen' => 99.00,
                'oee_persen' => 87.73,
                'produktivitas_persen' => 92.81,
                'status' => 'submitted',
                'notes' => 'Granulasi dan pencampuran homogen.',
            ],
        ];

        $seededReports = [];
        foreach ($lhpReports as $lr) {
            $seededReports[] = LaporanHarian::updateOrCreate(['report_number' => $lr['report_number']], $lr);
        }

        // 6. Seed Downtime Details
        DowntimeDetail::create([
            'laporan_harian_id' => $seededReports[0]->id,
            'alasan_downtime_id' => $reasons[0]->id,
            'kategori' => 'mesin',
            'alasan_nama' => $reasons[0]->name,
            'durasi_menit' => 30.0,
            'keterangan' => 'Suhu chiller naik di atas 45C, dilakukan sirkulasi air dingin.',
        ]);

        DowntimeDetail::create([
            'laporan_harian_id' => $seededReports[0]->id,
            'alasan_downtime_id' => $reasons[4]->id,
            'kategori' => 'operasional',
            'alasan_nama' => $reasons[4]->name,
            'durasi_menit' => 15.0,
            'keterangan' => 'Line clearance dan absensi shift 1.',
        ]);

        DowntimeDetail::create([
            'laporan_harian_id' => $seededReports[1]->id,
            'alasan_downtime_id' => $reasons[1]->id,
            'kategori' => 'mesin',
            'alasan_nama' => $reasons[1]->name,
            'durasi_menit' => 20.0,
            'keterangan' => 'Kalibrasi volume nozzle nomor 3.',
        ]);

        // 7. Seed Reject Details
        RejectDetail::create([
            'laporan_harian_id' => $seededReports[0]->id,
            'odoo_scrap_id' => 5011,
            'odoo_mo_id' => 1011,
            'jenis_reject' => 'Endapan Serat Kasar',
            'material_name' => 'Ekstrak Temulawak Cair',
            'jumlah' => 200,
            'satuan' => 'ML',
            'biaya_satuan' => 1200.0,
            'total_nilai_scrap' => 240000.0,
            'keterangan' => 'Penyaringan kedua tertahan di filter mesh 100.',
        ]);

        RejectDetail::create([
            'laporan_harian_id' => $seededReports[1]->id,
            'odoo_scrap_id' => 5012,
            'odoo_mo_id' => 1011,
            'jenis_reject' => 'Segel Induksi Cacat',
            'material_name' => 'Alufoil Seal Induction 38mm',
            'jumlah' => 120,
            'satuan' => 'PCS',
            'biaya_satuan' => 450.0,
            'total_nilai_scrap' => 54000.0,
            'keterangan' => 'Daya rekat panas kurang optimal pada botol awal.',
        ]);
    }
}
