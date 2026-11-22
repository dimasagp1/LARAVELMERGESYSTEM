<?php

namespace Tests\Feature;

use App\Core\Models\ErpFeatureRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleFeatureCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'admin@herbatech.com')->first();
    }

    public function test_feature_route_renders_with_real_records_and_kpis(): void
    {
        $response = $this->actingAs($this->user)->get('/hris/shifts');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/Common/ModuleFeatureWorkspace')
            ->has('records.data')
            ->has('summary.total_records')
            ->has('summary.approved_count')
            ->has('nextCode')
        );

        $this->assertDatabaseHas('erp_feature_records', [
            'feature_route' => '/hris/shifts',
        ]);
    }

    public function test_can_create_new_feature_record(): void
    {
        $response = $this->actingAs($this->user)->post('/modules-feature/records', [
            'feature_route' => '/hris/shifts',
            'title' => 'Shift Malam Ekstra Pabrik',
            'category' => 'Jadwal Kerja & Roster',
            'pic_name' => 'Budi Santoso',
            'entry_date' => now()->toDateString(),
            'status' => 'In Progress',
            'amount' => 5000000,
            'description' => 'Shift malam khusus periode lonjakan pesanan.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('erp_feature_records', [
            'feature_route' => '/hris/shifts',
            'title' => 'Shift Malam Ekstra Pabrik',
            'pic_name' => 'Budi Santoso',
            'status' => 'In Progress',
        ]);
    }

    public function test_can_update_existing_feature_record(): void
    {
        $record = ErpFeatureRecord::create([
            'module_slug' => 'hris',
            'feature_route' => '/hris/shifts',
            'record_code' => 'SHF-2026-9999',
            'title' => 'Shift Awal',
            'category' => 'Jadwal',
            'pic_name' => 'Operator',
            'status' => 'Draft',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->put("/modules-feature/records/{$record->id}", [
            'title' => 'Shift Diperbarui',
            'category' => 'Jadwal Kerja',
            'pic_name' => 'Manager HRD',
            'status' => 'Approved',
            'amount' => 7500000,
            'description' => 'Disetujui untuk implementasi minggu ini.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('erp_feature_records', [
            'id' => $record->id,
            'title' => 'Shift Diperbarui',
            'status' => 'Approved',
        ]);
    }

    public function test_can_delete_feature_record(): void
    {
        $record = ErpFeatureRecord::create([
            'module_slug' => 'hris',
            'feature_route' => '/hris/shifts',
            'record_code' => 'SHF-2026-8888',
            'title' => 'Shift Yang Dihapus',
            'category' => 'Jadwal',
            'status' => 'Draft',
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->delete("/modules-feature/records/{$record->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSoftDeleted('erp_feature_records', [
            'id' => $record->id,
        ]);
    }

    public function test_can_export_feature_records_as_csv(): void
    {
        ErpFeatureRecord::create([
            'module_slug' => 'hris',
            'feature_route' => '/hris/shifts',
            'record_code' => 'SHF-2026-0001',
            'title' => 'Shift Ekspor 1',
            'category' => 'Jadwal',
            'status' => 'Approved',
            'amount' => 1000000,
            'created_by' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/modules-feature/records/export?route=/hris/shifts');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
