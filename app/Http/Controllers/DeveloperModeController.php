<?php

namespace App\Http\Controllers;

use App\Core\Models\ApprovalWorkflow;
use App\Core\Models\CustomEntity;
use App\Core\Models\CustomField;
use App\Core\Models\ErpModule;
use App\Core\Services\CrossModuleDataService;
use App\Core\Services\NoCodeEngineService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class DeveloperModeController extends Controller
{
    public function __construct(
        protected NoCodeEngineService $noCodeEngine,
        protected CrossModuleDataService $crossModuleData
    ) {}

    public function index(): Response
    {
        $modules = ErpModule::with(['customEntities.fields', 'approvalWorkflows.steps', 'customWidgets'])
            ->orderBy('sort_order')
            ->get();

        $roles = Role::all(['id', 'name']);
        $users = User::all(['id', 'name', 'email', 'department_id']);

        return Inertia::render('DeveloperMode/Index', [
            'modules' => $modules,
            'roles' => $roles,
            'users' => $users,
            'dataSources' => $this->crossModuleData->getAvailableDataSources(),
            'availableMetrics' => $this->crossModuleData->getMetricsSummary(),
            'systemMenus' => $this->crossModuleData->getAllSystemMenusList(),
            'customEntitiesList' => $this->crossModuleData->getCustomEntitiesList(),
            'stats' => [
                'total_modules' => $modules->count(),
                'custom_modules' => $modules->where('is_custom', true)->count(),
                'total_entities' => CustomEntity::count(),
                'total_workflows' => ApprovalWorkflow::count(),
            ],
        ]);
    }

    public function storeModule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:100|unique:erp_modules,slug',
            'description' => 'nullable|string',
            'icon' => 'required|string',
            'color_theme' => 'required|string|in:emerald,amber,blue,violet,rose,teal,cyan,indigo',
            'roles' => 'nullable|array',
        ]);

        $this->noCodeEngine->createModule($validated, $request->user());

        return back()->with('success', "Modul baru '{$validated['name']}' berhasil dibuat dan terdaftar di App Launcher!");
    }

    public function storeEntity(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'module_id' => 'required|exists:erp_modules,id',
            'name' => 'required|string|max:150',
            'slug' => 'nullable|string|max:100',
            'icon' => 'required|string',
            'description' => 'nullable|string',
            'has_approval' => 'boolean',
            'has_audit_log' => 'boolean',
        ]);

        $entity = $this->noCodeEngine->createEntity((int) $validated['module_id'], $validated);

        return back()->with('success', "Entitas data '{$entity->name}' berhasil dibuat dengan starter fields.");
    }

    public function storeField(Request $request, int $entityId): RedirectResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:150',
            'field_key' => 'nullable|string|max:100',
            'field_type' => 'required|string|in:text,number,currency,date,select,relation,file,signature,boolean,textarea',
            'options_json' => 'nullable|array',
            'validation_rules' => 'nullable|string',
            'is_required' => 'boolean',
            'show_in_table' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $fieldId = $request->input('id');
        $this->noCodeEngine->saveField($entityId, $validated, $fieldId ? (int) $fieldId : null);

        return back()->with('success', "Field '{$validated['label']}' berhasil disimpan.");
    }

    public function deleteField(int $fieldId): RedirectResponse
    {
        CustomField::destroy($fieldId);

        return back()->with('success', 'Field berhasil dihapus.');
    }

    public function storeWorkflow(Request $request, int $moduleId): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'nullable|string|max:100',
            'target_model' => 'nullable|string',
            'min_amount' => 'nullable|numeric',
            'max_amount' => 'nullable|numeric',
            'steps' => 'required|array|min:1',
            'steps.*.step_name' => 'required|string',
            'steps.*.approver_type' => 'required|string|in:role,user,department_head',
            'steps.*.approver_role_id' => 'nullable|exists:roles,id',
            'steps.*.approver_user_id' => 'nullable|exists:users,id',
            'steps.*.sla_hours' => 'nullable|integer',
            'steps.*.requires_digital_signature' => 'boolean',
        ]);

        $workflow = $this->noCodeEngine->saveApprovalWorkflow($moduleId, $validated);

        return back()->with('success', "Alur persetujuan '{$workflow->name}' berhasil dikonfigurasi.");
    }

    public function storeWidgets(Request $request, int $moduleId): RedirectResponse
    {
        $validated = $request->validate([
            'widgets' => 'required|array',
            'widgets.*.title' => 'required|string',
            'widgets.*.widget_type' => 'required|string|in:kpi,chart,table,inbox',
            'widgets.*.data_source_config_json' => 'nullable|array',
            'widgets.*.grid_span' => 'integer|min:1|max:4',
        ]);

        $this->noCodeEngine->saveWidgets($moduleId, $validated['widgets']);

        return back()->with('success', 'Tata letak widget dashboard overview berhasil disimpan.');
    }

    /**
     * Save visual adjustable canvas layout and its widgets.
     */
    public function storeCanvas(Request $request, int $moduleId): RedirectResponse
    {
        $validated = $request->validate([
            'canvas_config' => 'required|array',
            'canvas_config.columns' => 'required|integer|min:1|max:4',
            'canvas_config.theme' => 'required|string',
            'canvas_config.card_radius' => 'required|string',
            'canvas_config.card_padding' => 'required|string',
            'canvas_config.card_elevation' => 'required|string',
            'widgets' => 'required|array',
            'widgets.*.title' => 'required|string',
            'widgets.*.widget_type' => 'required|string|in:kpi,chart,table,inbox,actions,banner,records',
            'widgets.*.data_source_config_json' => 'nullable|array',
            'widgets.*.grid_span' => 'integer|min:1|max:4',
        ]);

        $module = ErpModule::findOrFail($moduleId);
        $schema = $module->schema_config_json ?? [];
        $schema['canvas'] = $validated['canvas_config'];
        $module->schema_config_json = $schema;
        $module->save();

        // Save widgets
        $this->noCodeEngine->saveWidgets($moduleId, $validated['widgets']);

        return back()->with('success', "Desain tampilan kanvas modul '{$module->name}' berhasil disimpan dan aktif!");
    }

    /**
     * Fetch records from cross-module data source.
     */
    public function fetchSourceRecords(Request $request, string $sourceKey): JsonResponse
    {
        $search = $request->query('search');
        $limit = (int) $request->query('limit', 20);

        $records = $this->crossModuleData->fetchSourceRecords($sourceKey, $search, $limit);

        return response()->json($records);
    }

    /**
     * Query live stats, counts, and sample records for any selected menu or entity.
     */
    public function queryMenuData(Request $request): JsonResponse
    {
        $type = (string) $request->query('type', 'system_menu');
        $key = (string) $request->query('key', '');
        $status = $request->query('status');
        $aggregation = (string) $request->query('aggregation', 'count');
        $limit = (int) $request->query('limit', 5);

        $result = $this->crossModuleData->queryMenuData($type, $key, $status, $aggregation, $limit);

        return response()->json($result);
    }
}
