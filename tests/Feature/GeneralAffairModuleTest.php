<?php

namespace Tests\Feature;

use App\Core\Models\Department;
use App\Core\Models\Location;
use App\Core\Models\Plant;
use App\Models\User;
use App\Modules\GeneralAffair\Models\AssetCategory;
use App\Modules\GeneralAffair\Models\AtkItem;
use App\Modules\GeneralAffair\Models\AtkRequest;
use App\Modules\GeneralAffair\Models\CleaningCheckpoint;
use App\Modules\GeneralAffair\Models\FpuRequest;
use App\Modules\GeneralAffair\Models\GaAsset;
use App\Modules\GeneralAffair\Models\SecurityCheckpoint;
use App\Modules\GeneralAffair\Models\WorkOrder;
use Database\Seeders\CoreMasterDataSeeder;
use Database\Seeders\DefaultErpModulesSeeder;
use Database\Seeders\GeneralAffairModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeneralAffairModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultErpModulesSeeder::class);
        $this->seed(CoreMasterDataSeeder::class);
        $this->seed(GeneralAffairModuleSeeder::class);

        $this->user = User::where('email', 'admin@herbatech.com')->first() ?? User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_general_affair_overview_dashboard_is_accessible(): void
    {
        $response = $this->get('/general-affair');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/GeneralAffair/Overview')
            ->has('module')
            ->has('kpis')
            ->has('woStatusCounts')
            ->has('assetsByPlant')
            ->has('patrolSummary')
            ->has('pendingApprovals')
        );
    }

    public function test_work_order_form_a_submission_auto_numbers_and_saves(): void
    {
        $department = Department::first();
        $location = Location::first();

        $response = $this->post('/general-affair/work-orders', [
            'department_id' => $department->id,
            'location_id' => $location->id,
            'category' => 'Gedung & Sipil',
            'priority' => 'Tinggi',
            'complaint_description' => 'Kerusakan engsel pintu cleanroom ruang produksi ekstraksi.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ga_work_orders', [
            'department_id' => $department->id,
            'priority' => 'Tinggi',
            'status' => 'Menunggu Penugasan',
        ]);

        $wo = WorkOrder::where('complaint_description', 'like', '%cleanroom%')->first();
        $this->assertNotNull($wo);
        $this->assertStringStartsWith('WO-', $wo->wo_number);
    }

    public function test_work_order_triage_and_engineer_assignment(): void
    {
        $wo = WorkOrder::where('status', 'Menunggu Penugasan')->first() ?? WorkOrder::first();

        $response = $this->post("/general-affair/work-orders/{$wo->id}/assign", [
            'assigned_engineer_id' => $this->user->id,
        ]);

        $response->assertRedirect();
        $wo->refresh();
        $this->assertEquals($this->user->id, $wo->assigned_engineer_id);
        $this->assertEquals('Pengecekan Engineer', $wo->status);
    }

    public function test_work_order_form_b_and_automatic_form_c_fpu_creation(): void
    {
        $department = Department::first();
        $wo = WorkOrder::create([
            'wo_number' => 'WO-202611-9999',
            'user_id' => $this->user->id,
            'department_id' => $department->id,
            'complaint_description' => 'Test keluhan pompa sirkulasi berisik',
            'priority' => 'Normal',
            'category' => 'Gedung & Sipil',
            'status' => 'Pengecekan Engineer',
        ]);

        $response = $this->post("/general-affair/work-orders/{$wo->id}/check", [
            'check_result' => 'Ditemukan kebocoran seal bearing pompa sirkulasi.',
            'action_plan' => 'Ganti mechanical seal dan pelumasan bearing.',
            'needs_materials' => true,
            'materials' => [
                [
                    'material_name' => 'Mechanical Seal 2.5 Inch Silicon Carbide',
                    'quantity' => 1,
                    'estimated_unit_price' => 1250000,
                ],
            ],
        ]);

        $response->assertRedirect();
        $wo->refresh();
        $this->assertEquals('Menunggu Pengadaan Material', $wo->status);

        $fpu = FpuRequest::where('work_order_id', $wo->id)->first();
        $this->assertNotNull($fpu);
        $this->assertStringStartsWith('FPU-', $fpu->fpu_number);
        $this->assertDatabaseHas('ga_fpu_materials', [
            'fpu_request_id' => $fpu->id,
            'material_name' => 'Mechanical Seal 2.5 Inch Silicon Carbide',
        ]);
    }

    public function test_fpu_multi_tier_approval_flow(): void
    {
        $department = Department::first();
        $wo = WorkOrder::create([
            'wo_number' => 'WO-202611-8888',
            'user_id' => $this->user->id,
            'department_id' => $department->id,
            'complaint_description' => 'Perbaikan fan coil unit',
            'priority' => 'Normal',
            'category' => 'Gedung & Sipil',
            'status' => 'Menunggu Pengadaan Material',
        ]);

        $fpu = FpuRequest::create([
            'work_order_id' => $wo->id,
            'fpu_number' => 'FPU-202611-8888',
            'request_date' => now()->toDateString(),
            'status' => 'PENDING_HRGA',
        ]);

        // 1. HRGA Approval
        $response = $this->post("/general-affair/work-orders/fpu/{$fpu->id}/approve", [
            'tier' => 'hrga',
            'decision' => 'approve',
        ]);
        $response->assertRedirect();
        $fpu->refresh();
        $this->assertEquals('PENDING_MANAGER', $fpu->status);

        // 2. Manager Approval
        $response = $this->post("/general-affair/work-orders/fpu/{$fpu->id}/approve", [
            'tier' => 'manager',
            'decision' => 'approve',
        ]);
        $response->assertRedirect();
        $fpu->refresh();
        $this->assertEquals('PENDING_GM', $fpu->status);

        // 3. GM Approval
        $response = $this->post("/general-affair/work-orders/fpu/{$fpu->id}/approve", [
            'tier' => 'gm',
            'decision' => 'approve',
        ]);
        $response->assertRedirect();
        $fpu->refresh();
        $this->assertEquals('APPROVED', $fpu->status);

        // WO should automatically transition to Sedang Dikerjakan
        $this->assertEquals('Sedang Dikerjakan', $fpu->workOrder->fresh()->status);
    }

    public function test_work_order_progress_logging_and_handover_bast_completion(): void
    {
        $wo = WorkOrder::first();

        // 1. Log Progress
        $response = $this->post("/general-affair/work-orders/{$wo->id}/progress", [
            'progress_percentage' => 100,
            'notes' => 'Penggantian selesai dan pengetesan beban 1 jam berjalan sukses.',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('ga_work_order_progresses', [
            'work_order_id' => $wo->id,
            'progress_percent' => 100,
        ]);

        // 2. Prepare Handover BAST
        $response = $this->post("/general-affair/work-orders/{$wo->id}/handover", [
            'root_cause' => 'Seal aus usia pakai 3 tahun',
            'action_taken' => 'Penggantian part baru original Trane',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('ga_wo_handovers', [
            'work_order_id' => $wo->id,
        ]);

        // 3. Confirm Handover with Rating
        $response = $this->post("/general-affair/work-orders/{$wo->id}/confirm-handover", [
            'rating' => 5,
            'feedback' => 'Hasil pekerjaan sangat rapi dan dingin kembali maksimal.',
        ]);
        $response->assertRedirect();

        $wo->refresh();
        $this->assertEquals('Selesai', $wo->status);
        $this->assertEquals(5, $wo->handover->fresh()->satisfaction_rating);
    }

    public function test_atk_request_multi_level_approval_and_stock_deduction(): void
    {
        $atkItem = AtkItem::first();
        $initialStock = $atkItem->current_stock;
        $department = Department::first();

        // 1. Create Request
        $response = $this->post('/general-affair/atk', [
            'type' => 'ATK',
            'department_id' => $department->id,
            'alasan_pengajuan' => 'Kebutuhan ATK untuk tim validasi CPOB',
            'items' => [
                [
                    'atk_item_id' => $atkItem->id,
                    'quantity' => 5,
                ],
            ],
        ]);
        $response->assertRedirect();

        $req = AtkRequest::where('alasan_pengajuan', 'like', '%validasi CPOB%')->first();
        $this->assertNotNull($req);
        $this->assertStringStartsWith('ATK-', $req->request_number);

        // 2. Manager Approval
        $response = $this->post("/general-affair/atk/{$req->id}/approve-manager", [
            'action' => 'approve',
            'notes' => 'Disetujui manager',
        ]);
        $response->assertRedirect();

        // 3. Admin GA Approval
        $response = $this->post("/general-affair/atk/{$req->id}/approve-admin", [
            'notes' => 'Barang siap diserahkan',
        ]);
        $response->assertRedirect();

        // 4. Handover & Stock Deduction
        $response = $this->post("/general-affair/atk/{$req->id}/handover", [
            'receiver_name' => 'Budi Staff QC',
        ]);
        $response->assertRedirect();

        $atkItem->refresh();
        $this->assertEquals($initialStock - 5, $atkItem->current_stock);
        $this->assertEquals('HANDED_OVER', $req->fresh()->status);
    }

    public function test_atk_request_revision_flow_spawns_r1(): void
    {
        $original = AtkRequest::first();

        $response = $this->post("/general-affair/atk/{$original->id}/revision", [
            'alasan_pengajuan' => 'Revisi penyesuaian kuota kertas',
            'items' => [
                [
                    'atk_item_id' => $original->items->first()?->atk_item_id,
                    'quantity' => 3,
                ],
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals('REJECTED', $original->fresh()->status);

        $revision = AtkRequest::where('parent_id', $original->id)->first();
        $this->assertNotNull($revision);
        $this->assertStringContainsString('-R1', $revision->request_number);
    }

    public function test_fixed_asset_registration_and_straight_line_depreciation_calculation(): void
    {
        $plant = Plant::first();
        $dept = Department::first();
        $category = AssetCategory::first();

        // Test Auto Code & Creation
        $response = $this->post('/general-affair/assets', [
            'name' => 'Dehumidifier Cleanroom Boyolali',
            'short_name' => 'DHM',
            'plant_id' => $plant->id,
            'department_id' => $dept->id,
            'category_id' => $category->id,
            'classification' => 'UTAMA',
            'purchase_date' => now()->subMonths(12)->toDateString(),
            'purchase_cost' => 48000000,
            'useful_life_years' => 4,
            'condition' => 'BAIK',
            'pic_name' => 'Supriyadi',
        ]);

        $response->assertRedirect();
        $asset = GaAsset::where('name', 'like', '%Dehumidifier%')->first();
        $this->assertNotNull($asset);
        $this->assertStringStartsWith('HII-', $asset->asset_code);

        // Test Straight-Line Depreciation Accessors
        // 48.000.000 / (4 * 12 = 48 bulan) = 1.000.000 / bulan
        $this->assertEquals(1000000.00, $asset->monthly_depreciation);
        $this->assertGreaterThanOrEqual(12, $asset->months_used);
        $this->assertGreaterThanOrEqual(12000000.00, $asset->accumulated_depreciation);
        $this->assertLessThanOrEqual(36000000.00, $asset->book_value);
    }

    public function test_fixed_asset_mutation_and_dir_lampiran_c_report(): void
    {
        $asset = GaAsset::first();
        $plants = Plant::all();
        $targetPlant = $plants->last();
        $locations = Location::all();
        $targetLocation = $locations->last();

        // 1. Mutate Asset
        $response = $this->post("/general-affair/assets/{$asset->id}/mutate", [
            'to_plant_id' => $targetPlant->id,
            'to_department_id' => $asset->department_id,
            'to_location_id' => $targetLocation->id,
            'pic_receiver' => 'Pak Joko PIC Baru',
            'reason' => 'Kebutuhan perluasan kapasitas fasilitas',
        ]);
        $response->assertRedirect();

        $asset->refresh();
        $this->assertEquals($targetPlant->id, $asset->plant_id);
        $this->assertEquals($targetLocation->id, $asset->location_id);
        $this->assertEquals('Pak Joko PIC Baru', $asset->pic_name);
        $this->assertDatabaseHas('ga_asset_mutations', [
            'asset_id' => $asset->id,
            'pic_receiver' => 'Pak Joko PIC Baru',
        ]);

        // 2. Check DIR Lampiran C View
        $response = $this->get("/general-affair/assets/dir?location_id={$targetLocation->id}");
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/GeneralAffair/Assets/Dir')
            ->has('report')
            ->where('report.location.id', $targetLocation->id)
        );
    }

    public function test_patrol_security_and_cleaning_scan_logging(): void
    {
        $secCp = SecurityCheckpoint::first();
        $cleanCp = CleaningCheckpoint::first();

        // 1. Security Scan
        $response = $this->post('/general-affair/patrols/security/scan', [
            'qr_token' => $secCp->qr_token,
            'duration_seconds' => 600,
            'is_anomaly' => false,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('ga_security_checkpoint_logs', [
            'checkpoint_id' => $secCp->id,
            'is_anomaly' => false,
            'status' => 'Aman',
        ]);

        // 2. Cleaning Scan
        $response = $this->post('/general-affair/patrols/cleaning/scan', [
            'qr_token' => $cleanCp->qr_token,
            'is_sanitized' => true,
            'checklist_json' => [
                'lantai' => true,
                'tempat_sampah' => true,
                'aroma' => true,
                'refill_sabun' => true,
            ],
            'notes' => 'Ruangan telah disanitasi bersih',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('ga_cleaning_checkpoint_logs', [
            'checkpoint_id' => $cleanCp->id,
            'is_sanitized' => true,
        ]);
    }

    public function test_general_affair_pdf_stream_endpoints(): void
    {
        $wo = WorkOrder::first();
        $atk = AtkRequest::first();
        $loc = Location::first();

        // 1. Work Order Form A & D PDF
        $response = $this->get("/general-affair/work-orders/{$wo->id}/pdf");
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));

        // 2. Form C FPU PDF
        $response = $this->get("/general-affair/work-orders/{$wo->id}/pdf?type=fpu");
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));

        // 3. ATK Request PDF
        $response = $this->get("/general-affair/atk/{$atk->id}/pdf");
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));

        // 4. DIR Lampiran C PDF
        $response = $this->get("/general-affair/assets/dir/{$loc->id}/pdf");
        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
    }
}
