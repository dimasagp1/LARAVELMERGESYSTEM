<?php

namespace App\Http\Controllers;

use App\Core\Models\CustomEntity;
use App\Core\Models\CustomRecord;
use App\Core\Models\ErpModule;
use App\Core\Services\ModuleManagerService;
use App\Core\Services\NoCodeEngineService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DynamicEntityController extends Controller
{
    public function __construct(
        protected NoCodeEngineService $noCodeEngine,
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Overview page for custom modules.
     */
    public function overview(string $moduleSlug): Response
    {
        $module = ErpModule::where('slug', $moduleSlug)
            ->with(['customEntities.fields', 'customWidgets'])
            ->firstOrFail();

        $menus = $this->moduleManager->getModuleMenus($module->slug, $module->schema_config_json);

        return Inertia::render('Modules/Custom/Overview', [
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'description' => $module->description,
                'color_theme' => $module->color_theme,
                'icon' => $module->icon,
                'menus' => $menus,
            ],
            'entities' => $module->customEntities,
            'widgets' => $module->customWidgets,
        ]);
    }

    /**
     * Generic Index Table for any dynamic entity.
     */
    public function index(Request $request, string $moduleSlug, string $entitySlug): Response
    {
        $module = ErpModule::where('slug', $moduleSlug)->firstOrFail();
        $entity = CustomEntity::where('module_id', $module->id)
            ->where('slug', $entitySlug)
            ->with(['fields'])
            ->firstOrFail();

        $query = CustomRecord::where('entity_id', $entity->id)
            ->with(['creator', 'updater'])
            ->latest();

        // Optional search
        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search): void {
                $q->where('record_number', 'like', "%{$search}%")
                    ->orWhere('data_json', 'like', "%{$search}%");
            });
        }

        $records = $query->paginate(15)->withQueryString();
        $menus = $this->moduleManager->getModuleMenus($module->slug, $module->schema_config_json);

        return Inertia::render('Modules/Custom/DynamicEntityIndex', [
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
                'slug' => $module->slug,
                'color_theme' => $module->color_theme,
                'menus' => $menus,
            ],
            'entity' => $entity,
            'fields' => $entity->fields,
            'records' => $records,
            'stats' => [
                'total_records' => CustomRecord::where('entity_id', $entity->id)->count(),
                'draft_records' => CustomRecord::where('entity_id', $entity->id)->where('status', 'draft')->count(),
                'approved_records' => CustomRecord::where('entity_id', $entity->id)->where('status', 'approved')->count(),
            ],
        ]);
    }

    /**
     * Show create form for dynamic entity.
     */
    public function create(string $moduleSlug, string $entitySlug): Response
    {
        $module = ErpModule::where('slug', $moduleSlug)->firstOrFail();
        $entity = CustomEntity::where('module_id', $module->id)
            ->where('slug', $entitySlug)
            ->with(['fields'])
            ->firstOrFail();

        $menus = $this->moduleManager->getModuleMenus($module->slug, $module->schema_config_json);

        return Inertia::render('Modules/Custom/DynamicEntityForm', [
            'module' => [
                'name' => $module->name,
                'slug' => $module->slug,
                'color_theme' => $module->color_theme,
                'menus' => $menus,
            ],
            'entity' => $entity,
            'fields' => $entity->fields,
            'record' => null,
            'mode' => 'create',
        ]);
    }

    /**
     * Store new record.
     */
    public function store(Request $request, string $moduleSlug, string $entitySlug): RedirectResponse
    {
        $module = ErpModule::where('slug', $moduleSlug)->firstOrFail();
        $entity = CustomEntity::where('module_id', $module->id)
            ->where('slug', $entitySlug)
            ->with(['fields'])
            ->firstOrFail();

        $record = $this->noCodeEngine->saveRecord($entity, [
            'data_json' => $request->all(),
            'status' => $request->input('status', 'submitted'),
        ], null, $request->user());

        return redirect("/modules/{$moduleSlug}/entities/{$entitySlug}")
            ->with('success', "Dokumen {$record->record_number} berhasil disimpan!");
    }

    /**
     * Show edit form for dynamic entity.
     */
    public function edit(string $moduleSlug, string $entitySlug, int $recordId): Response
    {
        $module = ErpModule::where('slug', $moduleSlug)->firstOrFail();
        $entity = CustomEntity::where('module_id', $module->id)
            ->where('slug', $entitySlug)
            ->with(['fields'])
            ->firstOrFail();

        $record = CustomRecord::where('entity_id', $entity->id)->findOrFail($recordId);
        $menus = $this->moduleManager->getModuleMenus($module->slug, $module->schema_config_json);

        return Inertia::render('Modules/Custom/DynamicEntityForm', [
            'module' => [
                'name' => $module->name,
                'slug' => $module->slug,
                'color_theme' => $module->color_theme,
                'menus' => $menus,
            ],
            'entity' => $entity,
            'fields' => $entity->fields,
            'record' => $record,
            'mode' => 'edit',
        ]);
    }

    /**
     * Update existing record.
     */
    public function update(Request $request, string $moduleSlug, string $entitySlug, int $recordId): RedirectResponse
    {
        $module = ErpModule::where('slug', $moduleSlug)->firstOrFail();
        $entity = CustomEntity::where('module_id', $module->id)
            ->where('slug', $entitySlug)
            ->firstOrFail();

        $this->noCodeEngine->saveRecord($entity, [
            'data_json' => $request->all(),
            'status' => $request->input('status', 'submitted'),
        ], $recordId, $request->user());

        return redirect("/modules/{$moduleSlug}/entities/{$entitySlug}")
            ->with('success', 'Perubahan dokumen berhasil diperbarui!');
    }

    /**
     * Delete record.
     */
    public function destroy(string $moduleSlug, string $entitySlug, int $recordId): RedirectResponse
    {
        CustomRecord::destroy($recordId);

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }

    /**
     * Export records to PDF.
     */
    public function exportPdf(string $moduleSlug, string $entitySlug): HttpResponse
    {
        $module = ErpModule::where('slug', $moduleSlug)->firstOrFail();
        $entity = CustomEntity::where('module_id', $module->id)
            ->where('slug', $entitySlug)
            ->with(['fields'])
            ->firstOrFail();

        $records = CustomRecord::where('entity_id', $entity->id)->latest()->get();

        $pdf = Pdf::loadView('pdf.dynamic_entity', [
            'module' => $module,
            'entity' => $entity,
            'fields' => $entity->fields,
            'records' => $records,
        ]);

        return $pdf->download("{$entity->slug}_report.pdf");
    }

    /**
     * Export records to CSV / Excel.
     */
    public function exportExcel(string $moduleSlug, string $entitySlug): StreamedResponse
    {
        $module = ErpModule::where('slug', $moduleSlug)->firstOrFail();
        $entity = CustomEntity::where('module_id', $module->id)
            ->where('slug', $entitySlug)
            ->with(['fields'])
            ->firstOrFail();

        $records = CustomRecord::where('entity_id', $entity->id)->latest()->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$entity->slug}_export.csv\"",
        ];

        return response()->stream(function () use ($entity, $records): void {
            $handle = fopen('php://output', 'w');
            $headerRow = array_merge(['Nomor Dokumen', 'Status', 'Dibuat Pada'], $entity->fields->pluck('label')->toArray());
            fputcsv($handle, $headerRow);

            foreach ($records as $row) {
                $data = $row->data_json ?? [];
                $rowValues = [$row->record_number, $row->status, $row->created_at->format('Y-m-d H:i')];
                foreach ($entity->fields as $field) {
                    $rowValues[] = $data[$field->field_key] ?? '-';
                }
                fputcsv($handle, $rowValues);
            }
            fclose($handle);
        }, 200, $headers);
    }
}
