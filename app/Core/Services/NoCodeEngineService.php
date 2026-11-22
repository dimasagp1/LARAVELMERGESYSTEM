<?php

namespace App\Core\Services;

use App\Core\Models\ApprovalWorkflow;
use App\Core\Models\CustomDashboardWidget;
use App\Core\Models\CustomEntity;
use App\Core\Models\CustomField;
use App\Core\Models\CustomRecord;
use App\Core\Models\ErpModule;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NoCodeEngineService
{
    public function __construct(
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Create a new custom module from the UI without code changes.
     *
     * @param  array<string, mixed>  $data
     */
    public function createModule(array $data, ?User $user = null): ErpModule
    {
        $slug = Str::slug($data['slug'] ?? $data['name']);
        $routePrefix = "/modules/{$slug}";

        $module = ErpModule::create([
            'slug' => $slug,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? 'Folder',
            'color_theme' => $data['color_theme'] ?? 'emerald',
            'route_prefix' => $routePrefix,
            'is_active' => true,
            'is_core' => false,
            'is_custom' => true,
            'sort_order' => (ErpModule::max('sort_order') ?? 8) + 1,
            'schema_config_json' => [
                'roles' => $data['roles'] ?? ['Superadmin', 'System Architect'],
                'menus' => [
                    ['name' => 'Overview', 'route' => $routePrefix, 'icon' => 'LayoutDashboard'],
                ],
            ],
        ]);

        // Auto-generate permissions (PRD 3.2)
        $actions = ['view', 'create', 'edit', 'delete', 'approve'];
        foreach ($actions as $action) {
            $perm = Permission::firstOrCreate(['name' => "{$action}_{$slug}"]);
            // Assign to Superadmin & System Architect
            foreach (['Superadmin', 'System Architect'] as $roleName) {
                $role = Role::where('name', $roleName)->first();
                $role?->givePermissionTo($perm);
            }
        }

        $this->moduleManager->clearCache();

        return $module;
    }

    /**
     * Create a custom entity under a module.
     *
     * @param  array<string, mixed>  $data
     */
    public function createEntity(int $moduleId, array $data): CustomEntity
    {
        $slug = Str::slug($data['slug'] ?? $data['name']);

        $entity = CustomEntity::create([
            'module_id' => $moduleId,
            'name' => $data['name'],
            'slug' => $slug,
            'table_type' => $data['table_type'] ?? 'jsonb',
            'icon' => $data['icon'] ?? 'Folder',
            'description' => $data['description'] ?? null,
            'has_approval' => (bool) ($data['has_approval'] ?? false),
            'has_audit_log' => (bool) ($data['has_audit_log'] ?? true),
        ]);

        // Automatically seed default starter fields
        $starterFields = [
            [
                'field_key' => 'title',
                'label' => 'Judul / Nama Dokumen',
                'field_type' => 'text',
                'is_required' => true,
                'show_in_table' => true,
                'sort_order' => 1,
            ],
            [
                'field_key' => 'category',
                'label' => 'Kategori',
                'field_type' => 'select',
                'options_json' => ['Umum', 'Prioritas', 'Khusus'],
                'is_required' => false,
                'show_in_table' => true,
                'sort_order' => 2,
            ],
            [
                'field_key' => 'date',
                'label' => 'Tanggal Transaksi',
                'field_type' => 'date',
                'is_required' => true,
                'show_in_table' => true,
                'sort_order' => 3,
            ],
            [
                'field_key' => 'notes',
                'label' => 'Catatan / Deskripsi',
                'field_type' => 'text',
                'is_required' => false,
                'show_in_table' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($starterFields as $f) {
            $entity->fields()->create($f);
        }

        // Add menu to module's schema_config_json
        $module = ErpModule::find($moduleId);
        if ($module) {
            $config = $module->schema_config_json ?? [];
            $menus = $config['menus'] ?? [];
            $menus[] = [
                'name' => $entity->name,
                'route' => "{$module->route_prefix}/entities/{$entity->slug}",
                'icon' => $entity->icon,
            ];
            $config['menus'] = $menus;
            $module->schema_config_json = $config;
            $module->save();
            $this->moduleManager->clearCache();
        }

        return $entity;
    }

    /**
     * Add or update a custom field.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveField(int $entityId, array $data, ?int $fieldId = null): CustomField
    {
        $fieldKey = Str::snake($data['field_key'] ?? $data['label']);

        return CustomField::updateOrCreate(
            [
                'id' => $fieldId,
                'entity_id' => $entityId,
            ],
            [
                'field_key' => $fieldKey,
                'label' => $data['label'],
                'field_type' => $data['field_type'],
                'options_json' => $data['options_json'] ?? null,
                'validation_rules' => $data['validation_rules'] ?? null,
                'is_required' => (bool) ($data['is_required'] ?? false),
                'show_in_table' => (bool) ($data['show_in_table'] ?? true),
                'sort_order' => $data['sort_order'] ?? 0,
            ]
        );
    }

    /**
     * Save dynamic record for an entity.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveRecord(CustomEntity $entity, array $data, ?int $recordId = null, ?User $user = null): CustomRecord
    {
        $recordNumber = $data['record_number'] ?? null;
        if (! $recordNumber) {
            $count = CustomRecord::where('entity_id', $entity->id)->count() + 1;
            $prefix = strtoupper(substr($entity->slug, 0, 4));
            $recordNumber = sprintf('REC-%s-%s-%04d', $prefix, date('Y'), $count);
        }

        return CustomRecord::updateOrCreate(
            [
                'id' => $recordId,
                'entity_id' => $entity->id,
            ],
            [
                'record_number' => $recordNumber,
                'data_json' => $data['data_json'] ?? $data,
                'status' => $data['status'] ?? 'draft',
                'created_by' => $user?->id,
                'updated_by' => $user?->id,
            ]
        );
    }

    /**
     * Save or update an approval workflow without coding.
     *
     * @param  array<string, mixed>  $data
     */
    public function saveApprovalWorkflow(int $moduleId, array $data): ApprovalWorkflow
    {
        $code = ! empty($data['code']) ? strtoupper(trim($data['code'])) : 'WF_CUSTOM_'.time();

        $workflow = ApprovalWorkflow::updateOrCreate(
            ['id' => $data['id'] ?? null],
            [
                'module_id' => $moduleId,
                'name' => $data['name'],
                'code' => $code,
                'target_model' => $data['target_model'] ?? CustomRecord::class,
                'min_amount' => $data['min_amount'] ?? null,
                'max_amount' => $data['max_amount'] ?? null,
                'is_active' => true,
            ]
        );

        if (! empty($data['steps'])) {
            $workflow->steps()->delete();
            foreach ($data['steps'] as $idx => $step) {
                $workflow->steps()->create([
                    'step_order' => $idx + 1,
                    'step_name' => $step['step_name'],
                    'approver_type' => $step['approver_type'] ?? 'role',
                    'approver_role_id' => $step['approver_role_id'] ?? null,
                    'approver_user_id' => $step['approver_user_id'] ?? null,
                    'sla_hours' => $step['sla_hours'] ?? 24,
                    'requires_digital_signature' => (bool) ($step['requires_digital_signature'] ?? false),
                    'can_reject' => true,
                    'can_revise' => true,
                ]);
            }
        }

        return $workflow;
    }

    /**
     * Save custom dashboard widgets for an overview page.
     *
     * @param  list<array<string, mixed>>  $widgets
     */
    public function saveWidgets(int $moduleId, array $widgets): void
    {
        CustomDashboardWidget::where('module_id', $moduleId)->delete();

        foreach ($widgets as $idx => $w) {
            CustomDashboardWidget::create([
                'module_id' => $moduleId,
                'widget_type' => $w['widget_type'] ?? 'kpi',
                'title' => $w['title'],
                'data_source_config_json' => $w['data_source_config_json'] ?? null,
                'grid_span' => $w['grid_span'] ?? 1,
                'sort_order' => $idx + 1,
            ]);
        }
    }
}
