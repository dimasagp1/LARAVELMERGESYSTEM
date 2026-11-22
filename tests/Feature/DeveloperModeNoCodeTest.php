<?php

namespace Tests\Feature;

use App\Core\Models\CustomEntity;
use App\Core\Models\CustomField;
use App\Core\Models\CustomRecord;
use App\Core\Models\ErpModule;
use App\Models\User;
use Database\Seeders\CoreMasterDataSeeder;
use Database\Seeders\DefaultErpModulesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DeveloperModeNoCodeTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultErpModulesSeeder::class);
        $this->seed(CoreMasterDataSeeder::class);

        $superadminRole = Role::firstOrCreate(['name' => 'Superadmin']);
        $this->superadmin = User::firstOrCreate(
            ['email' => 'admin@herbatech.com'],
            [
                'name' => 'Super Administrator',
                'password' => bcrypt('password'),
                'status' => 'active',
            ]
        );
        $this->superadmin->assignRole($superadminRole);
    }

    public function test_developer_mode_index_screen_renders_for_superadmin(): void
    {
        $response = $this->actingAs($this->superadmin)->get('/developer-mode');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('DeveloperMode/Index')
            ->has('modules')
            ->has('roles')
            ->has('users')
            ->has('stats')
        );
    }

    public function test_superadmin_can_create_custom_module(): void
    {
        $payload = [
            'name' => 'Asset & Maintenance',
            'slug' => 'asset-maintenance',
            'description' => 'Modul pengelolaan aset mesin dan jadwal preventive maintenance.',
            'icon' => 'Wrench',
            'color_theme' => 'amber',
            'roles' => ['Superadmin'],
        ];

        $response = $this->actingAs($this->superadmin)->post('/developer-mode/modules', $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('erp_modules', [
            'slug' => 'asset-maintenance',
            'is_custom' => true,
            'is_active' => true,
            'color_theme' => 'amber',
        ]);
    }

    public function test_superadmin_can_create_custom_entity_and_fields(): void
    {
        $module = ErpModule::where('slug', 'production')->first();

        // 1. Create Entity
        $entityPayload = [
            'module_id' => $module->id,
            'name' => 'Work Order Mesin',
            'slug' => 'work-order-mesin',
            'icon' => 'Wrench',
            'description' => 'Instruksi kerja perbaikan mesin ekstraksi herbal.',
            'has_approval' => true,
            'has_audit_log' => true,
        ];

        $resEntity = $this->actingAs($this->superadmin)->post('/developer-mode/entities', $entityPayload);
        $resEntity->assertRedirect();

        $entity = CustomEntity::where('slug', 'work-order-mesin')->first();
        $this->assertNotNull($entity);
        $this->assertEquals($module->id, $entity->module_id);

        // Starter fields should exist
        $this->assertDatabaseHas('custom_fields', [
            'entity_id' => $entity->id,
            'field_key' => 'title',
        ]);

        // 2. Add Custom Field (Currency & Signature)
        $fieldPayload = [
            'label' => 'Estimasi Biaya',
            'field_key' => 'estimated_cost',
            'field_type' => 'currency',
            'is_required' => true,
            'show_in_table' => true,
            'sort_order' => 3,
        ];

        $resField = $this->actingAs($this->superadmin)->post("/developer-mode/entities/{$entity->id}/fields", $fieldPayload);
        $resField->assertRedirect();

        $this->assertDatabaseHas('custom_fields', [
            'entity_id' => $entity->id,
            'field_key' => 'estimated_cost',
            'field_type' => 'currency',
            'is_required' => true,
        ]);
    }

    public function test_superadmin_can_configure_workflow_and_widgets(): void
    {
        $module = ErpModule::where('slug', 'production')->first();
        $role = Role::firstOrCreate(['name' => 'Manager']);

        // 1. Workflow
        $workflowPayload = [
            'name' => 'Persetujuan Work Order',
            'code' => 'WF-WO-01',
            'min_amount' => 5000000,
            'steps' => [
                [
                    'step_name' => 'Review Supervisor Maintenance',
                    'approver_type' => 'role',
                    'approver_role_id' => $role->id,
                    'sla_hours' => 24,
                    'requires_digital_signature' => false,
                ],
                [
                    'step_name' => 'Approval Plant Manager',
                    'approver_type' => 'role',
                    'approver_role_id' => $role->id,
                    'sla_hours' => 48,
                    'requires_digital_signature' => true,
                ],
            ],
        ];

        $resWf = $this->actingAs($this->superadmin)->post("/developer-mode/modules/{$module->id}/workflows", $workflowPayload);
        $resWf->assertRedirect();

        $this->assertDatabaseHas('approval_workflows', [
            'module_id' => $module->id,
            'code' => 'WF-WO-01',
        ]);
        $this->assertDatabaseCount('approval_steps', 2);

        // 2. Dashboard Widgets
        $widgetsPayload = [
            'widgets' => [
                [
                    'title' => 'Total Work Order Aktif',
                    'widget_type' => 'kpi',
                    'grid_span' => 1,
                    'data_source_config_json' => ['metric' => 'count'],
                ],
                [
                    'title' => 'Grafik Downtime Mesin',
                    'widget_type' => 'chart',
                    'grid_span' => 2,
                    'data_source_config_json' => ['chart_type' => 'bar'],
                ],
            ],
        ];

        $resWidgets = $this->actingAs($this->superadmin)->post("/developer-mode/modules/{$module->id}/widgets", $widgetsPayload);
        $resWidgets->assertRedirect();

        $this->assertDatabaseHas('custom_dashboard_widgets', [
            'module_id' => $module->id,
            'title' => 'Total Work Order Aktif',
            'widget_type' => 'kpi',
        ]);
    }

    public function test_dynamic_entity_runtime_crud_operations(): void
    {
        $module = ErpModule::where('slug', 'production')->first();
        $entity = CustomEntity::create([
            'module_id' => $module->id,
            'name' => 'Kalibrasi Alat Lab',
            'slug' => 'kalibrasi-alat-lab',
            'table_type' => 'custom_records',
            'icon' => 'FlaskConical',
            'has_approval' => true,
            'has_audit_log' => true,
        ]);

        CustomField::create([
            'entity_id' => $entity->id,
            'field_key' => 'device_name',
            'label' => 'Nama Alat',
            'field_type' => 'text',
            'is_required' => true,
            'show_in_table' => true,
            'sort_order' => 1,
        ]);

        CustomField::create([
            'entity_id' => $entity->id,
            'field_key' => 'cost',
            'label' => 'Biaya Kalibrasi',
            'field_type' => 'currency',
            'is_required' => false,
            'show_in_table' => true,
            'sort_order' => 2,
        ]);

        // 1. Index Page
        $resIndex = $this->actingAs($this->superadmin)
            ->get("/modules/{$module->slug}/entities/{$entity->slug}");
        $resIndex->assertStatus(200);
        $resIndex->assertInertia(fn ($page) => $page
            ->component('Modules/Custom/DynamicEntityIndex')
            ->has('entity')
            ->has('fields')
            ->has('records')
        );

        // 2. Create Page
        $resCreate = $this->actingAs($this->superadmin)
            ->get("/modules/{$module->slug}/entities/{$entity->slug}/create");
        $resCreate->assertStatus(200);
        $resCreate->assertInertia(fn ($page) => $page
            ->component('Modules/Custom/DynamicEntityForm')
            ->where('mode', 'create')
        );

        // 3. Store Record
        $storePayload = [
            'device_name' => 'HPLC Shimadzu LC-20AD',
            'cost' => 12500000,
            'status' => 'submitted',
        ];

        $resStore = $this->actingAs($this->superadmin)
            ->post("/modules/{$module->slug}/entities/{$entity->slug}", $storePayload);
        $resStore->assertRedirect("/modules/{$module->slug}/entities/{$entity->slug}");

        $record = CustomRecord::where('entity_id', $entity->id)->first();
        $this->assertNotNull($record);
        $this->assertEquals('HPLC Shimadzu LC-20AD', $record->data_json['device_name']);
        $this->assertEquals(12500000, $record->data_json['cost']);
        $this->assertStringStartsWith('REC-', $record->record_number);

        // 4. Edit Page
        $resEdit = $this->actingAs($this->superadmin)
            ->get("/modules/{$module->slug}/entities/{$entity->slug}/{$record->id}/edit");
        $resEdit->assertStatus(200);
        $resEdit->assertInertia(fn ($page) => $page
            ->component('Modules/Custom/DynamicEntityForm')
            ->where('mode', 'edit')
            ->has('record')
        );

        // 5. Update Record
        $updatePayload = [
            'device_name' => 'HPLC Shimadzu LC-20AD (Updated)',
            'cost' => 15000000,
            'status' => 'approved',
        ];

        $resUpdate = $this->actingAs($this->superadmin)
            ->put("/modules/{$module->slug}/entities/{$entity->slug}/{$record->id}", $updatePayload);
        $resUpdate->assertRedirect("/modules/{$module->slug}/entities/{$entity->slug}");

        $record->refresh();
        $this->assertEquals('HPLC Shimadzu LC-20AD (Updated)', $record->data_json['device_name']);
        $this->assertEquals('approved', $record->status);

        // 6. Delete Record
        $resDelete = $this->actingAs($this->superadmin)
            ->delete("/modules/{$module->slug}/entities/{$entity->slug}/{$record->id}");
        $resDelete->assertRedirect();
        $this->assertSoftDeleted('custom_records', ['id' => $record->id]);
    }

    public function test_export_pdf_and_excel_dynamic_records(): void
    {
        $module = ErpModule::where('slug', 'quality')->first();
        $entity = CustomEntity::create([
            'module_id' => $module->id,
            'name' => 'Audit Sanitasi Ruang Bersih',
            'slug' => 'audit-sanitasi',
            'table_type' => 'custom_records',
            'icon' => 'ShieldCheck',
        ]);

        CustomField::create([
            'entity_id' => $entity->id,
            'field_key' => 'location',
            'label' => 'Lokasi Ruang',
            'field_type' => 'text',
            'show_in_table' => true,
            'sort_order' => 1,
        ]);

        CustomRecord::create([
            'entity_id' => $entity->id,
            'record_number' => 'REC-AUD-001',
            'data_json' => ['location' => 'Ruang Ekstraksi A'],
            'status' => 'approved',
            'created_by' => $this->superadmin->id,
        ]);

        // PDF export
        $resPdf = $this->actingAs($this->superadmin)
            ->get("/modules/{$module->slug}/entities/{$entity->slug}/export/pdf");
        $resPdf->assertStatus(200);
        $this->assertEquals('application/pdf', $resPdf->headers->get('content-type'));

        // Excel / CSV export
        $resExcel = $this->actingAs($this->superadmin)
            ->get("/modules/{$module->slug}/entities/{$entity->slug}/export/excel");
        $resExcel->assertStatus(200);
        $this->assertTrue(
            str_contains((string) $resExcel->headers->get('content-type'), 'text/csv') ||
            str_contains((string) $resExcel->headers->get('content-disposition'), '.csv')
        );
    }
}
