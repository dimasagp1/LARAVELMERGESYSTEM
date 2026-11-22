<?php

namespace Tests\Feature;

use App\Core\Models\Product;
use App\Models\User;
use App\Modules\Production\Models\LaporanHarian;
use App\Modules\Production\Models\ProductionDowntimeReason;
use App\Modules\Production\Models\ProductionLine;
use App\Modules\Production\Models\ProductionMachine;
use App\Modules\Production\Models\RejectDetail;
use App\Modules\Production\Models\WeeklyPlan;
use Database\Seeders\CoreMasterDataSeeder;
use Database\Seeders\DefaultErpModulesSeeder;
use Database\Seeders\ProductionModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Product $product;

    protected ProductionLine $line;

    protected ProductionMachine $machine;

    protected ProductionDowntimeReason $downtimeReason;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultErpModulesSeeder::class);
        $this->seed(CoreMasterDataSeeder::class);
        $this->seed(ProductionModuleSeeder::class);

        $this->user = User::where('email', 'admin@herbatech.com')->first();
        $this->product = Product::first();
        $this->line = ProductionLine::first();
        $this->machine = ProductionMachine::first();
        $this->downtimeReason = ProductionDowntimeReason::first();
    }

    public function test_production_overview_dashboard_renders(): void
    {
        $response = $this->actingAs($this->user)->get('/production');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/Produksi/Overview')
            ->has('metrics.kpis')
            ->has('metrics.heatGrid')
            ->has('metrics.paretoDowntime')
            ->has('metrics.batchBreakdown')
        );
    }

    public function test_wps_schedule_creation_with_odoo_guard_and_estafet_rules(): void
    {
        $batchNumber = 'TEST-EXT-999';

        // 1. Odoo MO Status Guard: Should fail if mo_status is cancelled
        $invalidOdooResponse = $this->actingAs($this->user)->post('/production/schedules', [
            'produk_id' => $this->product->id,
            'line_id' => $this->line->id,
            'proses' => 'mixing',
            'batch_number' => $batchNumber,
            'mo_status' => 'cancelled',
            'mp_count' => 10,
            'multiplier' => 2000,
            'tanggal' => now()->toDateString(),
            'shift' => 'shift1',
        ]);

        $invalidOdooResponse->assertSessionHas('error');

        // 2. Estafet Rule: Filling cannot be created before Mixing
        $invalidFillingResponse = $this->actingAs($this->user)->post('/production/schedules', [
            'produk_id' => $this->product->id,
            'line_id' => $this->line->id,
            'proses' => 'filling',
            'batch_number' => $batchNumber,
            'mo_status' => 'confirmed',
            'mp_count' => 10,
            'multiplier' => 2000,
            'tanggal' => now()->toDateString(),
            'shift' => 'shift1',
        ]);

        $invalidFillingResponse->assertSessionHas('error');

        // 3. Valid Mixing schedule creation
        $validMixingResponse = $this->actingAs($this->user)->post('/production/schedules', [
            'produk_id' => $this->product->id,
            'line_id' => $this->line->id,
            'proses' => 'mixing',
            'batch_number' => $batchNumber,
            'mo_status' => 'confirmed',
            'mp_count' => 10,
            'multiplier' => 2000,
            'tanggal' => now()->toDateString(),
            'shift' => 'shift1',
        ]);

        $validMixingResponse->assertRedirect('/production/schedules');

        $this->assertDatabaseHas('weekly_plans', [
            'batch_number' => $batchNumber,
            'proses' => 'mixing',
            'target_output' => 20000, // 10 MP * 2000
        ]);

        // 4. Now Filling is allowed because Mixing exists
        $validFillingResponse = $this->actingAs($this->user)->post('/production/schedules', [
            'produk_id' => $this->product->id,
            'line_id' => $this->line->id,
            'proses' => 'filling',
            'batch_number' => $batchNumber,
            'mo_status' => 'confirmed',
            'mp_count' => 8,
            'multiplier' => 2000,
            'tanggal' => now()->toDateString(),
            'shift' => 'shift1',
        ]);

        $validFillingResponse->assertRedirect('/production/schedules');
        $this->assertDatabaseHas('weekly_plans', [
            'batch_number' => $batchNumber,
            'proses' => 'filling',
            'target_output' => 16000, // 8 MP * 2000
        ]);
    }

    public function test_wps_packing_hold_toggle(): void
    {
        $plan = WeeklyPlan::where('proses', 'mixing')->first();

        $this->assertFalse((bool) $plan->packing_hold);

        $response = $this->actingAs($this->user)->post("/production/schedules/{$plan->id}/toggle-hold", [
            'is_hold' => true,
            'reason' => 'Menunggu pasokan dus sekunder',
        ]);

        $response->assertSessionHas('success');

        $plan->refresh();
        $this->assertTrue((bool) $plan->packing_hold);
        $this->assertStringContainsString('Packing DI-HOLD', $plan->notes);
    }

    public function test_laporan_harian_timer_lifecycle_and_auto_chaining(): void
    {
        $plan = WeeklyPlan::where('proses', 'mixing')->first();

        // 1. Create Laporan Harian
        $createResponse = $this->actingAs($this->user)->post('/production/daily-reports', [
            'weekly_plan_id' => $plan->id,
            'mesin_id' => $this->machine->id,
            'tanggal' => now()->toDateString(),
            'shift' => 'shift1',
            'notes' => 'Test Eksekusi LHP',
        ]);

        $createResponse->assertRedirect();

        $report = LaporanHarian::where('weekly_plan_id', $plan->id)->latest('id')->first();
        $this->assertNotNull($report);
        $this->assertEquals('draft', $report->timer_status);

        // 2. Start Timer
        $this->actingAs($this->user)->post("/production/daily-reports/{$report->id}/start");
        $report->refresh();
        $this->assertEquals('start', $report->timer_status);
        $this->assertNotNull($report->start_time);

        // 3. Pause Timer
        $this->actingAs($this->user)->post("/production/daily-reports/{$report->id}/pause");
        $report->refresh();
        $this->assertEquals('pause', $report->timer_status);
        $this->assertNotNull($report->pause_started_at);

        // 4. Resume Timer with Mandatory Downtime Reason
        $resumeResponse = $this->actingAs($this->user)->post("/production/daily-reports/{$report->id}/resume", [
            'alasan_downtime_id' => $this->downtimeReason->id,
            'kategori' => 'mesin',
            'alasan_nama' => 'Pembersihan Nozzle Mesin',
            'durasi_menit' => 20.0,
            'keterangan' => 'Kalibrasi presisi nozzle',
        ]);

        $resumeResponse->assertSessionHas('success');
        $report->refresh();
        $this->assertEquals('start', $report->timer_status);
        $this->assertNull($report->pause_started_at);
        $this->assertEquals(20, $report->total_pause_menit);

        $this->assertDatabaseHas('downtime_details', [
            'laporan_harian_id' => $report->id,
            'alasan_nama' => 'Pembersihan Nozzle Mesin',
            'durasi_menit' => 20.0,
        ]);

        // 5. End Timer & Auto-Chaining Next Stage (Mixing -> Filling)
        $this->actingAs($this->user)->post("/production/daily-reports/{$report->id}/end");
        $report->refresh();
        $this->assertEquals('end', $report->timer_status);
        $this->assertNotNull($report->end_time);
        $this->assertGreaterThanOrEqual(1, $report->gross_time_menit);

        // Verify Auto-chained next stage exists for Filling
        $chainedFillingPlan = WeeklyPlan::where('produk_id', $report->produk_id)
            ->where('batch_number', $report->batch_number)
            ->where('proses', 'filling')
            ->first();

        $this->assertNotNull($chainedFillingPlan);
    }

    public function test_output_submission_oee_calculation_and_supervisor_lock(): void
    {
        $report = LaporanHarian::where('proses', 'mixing')->first();
        $report->update([
            'gross_time_menit' => 480,
            'total_pause_menit' => 45,
            'ct' => 1.50,
        ]);

        // Submit Output
        $submitResponse = $this->actingAs($this->user)->post("/production/daily-reports/{$report->id}/submit", [
            'capacity_fisik' => 20000,
            'output_fisik' => 19500,
            'total_reject' => 150,
            'notes' => 'Pengerjaan selesai',
        ]);

        $submitResponse->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals('submitted', $report->status);
        $this->assertEquals(19500, $report->output_fisik);
        $this->assertGreaterThan(0, (float) $report->availability_persen);
        $this->assertGreaterThan(0, (float) $report->performance_persen);
        $this->assertGreaterThan(0, (float) $report->yield_persen);
        $this->assertGreaterThan(0, (float) $report->oee_persen);

        // Supervisor Lock
        $lockResponse = $this->actingAs($this->user)->post("/production/daily-reports/{$report->id}/lock");
        $lockResponse->assertSessionHas('success');

        $report->refresh();
        $this->assertEquals('locked', $report->status);
        $this->assertNotNull($report->locked_at);
        $this->assertTrue($report->is_locked);
    }

    public function test_reject_scrap_recording_and_odoo_sync(): void
    {
        $report = LaporanHarian::first();

        $postData = [
            'laporan_harian_id' => $report->id,
            'jenis_reject' => 'Segel Induksi Bocor',
            'material_name' => 'Alufoil Induction 38mm',
            'jumlah' => 50,
            'satuan' => 'PCS',
            'biaya_satuan' => 500,
            'keterangan' => 'Suhu pemanas kurang optimal',
        ];

        $response = $this->actingAs($this->user)->post('/production/scrap', $postData);
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reject_details', [
            'laporan_harian_id' => $report->id,
            'jenis_reject' => 'Segel Induksi Bocor',
            'jumlah' => 50,
            'total_nilai_scrap' => 25000,
        ]);

        $reject = RejectDetail::where('jenis_reject', 'Segel Induksi Bocor')->first();
        $this->assertNotNull($reject->odoo_scrap_id);
    }

    public function test_external_api_endpoints_for_hris_and_finance(): void
    {
        // 1. HRIS Endpoint: /api/v1/production/metrics
        $hrisResponse = $this->getJson('/api/v1/production/metrics?month='.now()->format('Y-m'));
        $hrisResponse->assertStatus(200);
        $hrisResponse->assertJsonStructure([
            'status',
            'period',
            'summary' => [
                'batch_count',
                'avg_oee_percent',
                'avg_availability_percent',
                'avg_performance_percent',
                'avg_yield_percent',
                'avg_productivity_percent',
                'total_output_units',
                'total_downtime_hours',
            ],
            'timestamp',
        ]);

        // 2. Finance Endpoint: /api/v1/production/finance-feed
        $financeResponse = $this->getJson('/api/v1/production/finance-feed?month='.now()->format('Y-m'));
        $financeResponse->assertStatus(200);
        $financeResponse->assertJsonStructure([
            'status',
            'period',
            'feed' => [
                'total_scrap_loss_rupiah',
                'total_downtime_minutes',
                'estimated_downtime_loss_rupiah',
                'total_manufacturing_loss_rupiah',
            ],
            'timestamp',
        ]);
    }

    public function test_pdf_exports_for_spk_and_lhp(): void
    {
        $plan = WeeklyPlan::first();
        $report = LaporanHarian::first();

        // 1. SPK PDF
        $spkPdf = $this->actingAs($this->user)->get("/production/schedules/{$plan->id}/pdf");
        $spkPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $spkPdf->headers->get('content-type'));

        // 2. LHP PDF
        $lhpPdf = $this->actingAs($this->user)->get("/production/daily-reports/{$report->id}/pdf");
        $lhpPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $lhpPdf->headers->get('content-type'));
    }
}
