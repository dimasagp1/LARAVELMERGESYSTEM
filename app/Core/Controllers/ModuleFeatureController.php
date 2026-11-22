<?php

namespace App\Core\Controllers;

use App\Core\Models\ErpFeatureRecord;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ModuleFeatureController extends Controller
{
    /**
     * Render the feature workspace for a registered menu route with full dynamic CRUD support.
     */
    public function render(Request $request, ModuleManagerService $moduleManager): Response
    {
        $path = '/'.trim($request->path(), '/');
        $segments = explode('/', trim($request->path(), '/'));
        $moduleSlug = $segments[0] ?? '';
        if ($moduleSlug === 'produksi') {
            $moduleSlug = 'production';
        }

        $allMenus = $moduleManager->getModuleMenus($moduleSlug);
        $currentMenu = collect($allMenus)->firstWhere('route', $path) ?? [
            'name' => ucwords(str_replace(['-', '/'], [' ', ' '], end($segments))),
            'route' => $path,
            'icon' => 'Layers',
            'category' => 'Operasional',
            'description' => 'Workspace & Manajemen Operasional Fitur Terpadu',
        ];

        // Auto-seed initial realistic demo records if this route has zero records yet
        $this->ensureInitialRecords($moduleSlug, $path, $currentMenu);

        // Build query for real records
        $query = ErpFeatureRecord::query()
            ->where('feature_route', $path)
            ->with(['creator', 'updater']);

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('record_code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $records = $query->latest('id')->paginate(10)->withQueryString();

        // Calculate summary counters from database
        $baseQuery = ErpFeatureRecord::where('feature_route', $path);
        $totalRecords = (clone $baseQuery)->count();
        $approvedCount = (clone $baseQuery)->where('status', 'Approved')->count();
        $pendingCount = (clone $baseQuery)->where('status', 'Pending Review')->count();
        $inProgressCount = (clone $baseQuery)->where('status', 'In Progress')->count();
        $draftCount = (clone $baseQuery)->where('status', 'Draft')->count();

        $complianceRate = $totalRecords > 0
            ? round(($approvedCount / $totalRecords) * 100, 1)
            : 100.0;

        $nextCode = ErpFeatureRecord::generateNextCode($moduleSlug, $path);

        return Inertia::render('Modules/Common/ModuleFeatureWorkspace', [
            'module' => [
                'name' => ucwords(str_replace('-', ' ', $moduleSlug)),
                'slug' => $moduleSlug,
                'menus' => $allMenus,
            ],
            'feature' => $currentMenu,
            'category' => $currentMenu['category'] ?? 'Operasional',
            'records' => $records,
            'summary' => [
                'total_records' => $totalRecords,
                'approved_count' => $approvedCount,
                'pending_count' => $pendingCount,
                'in_progress_count' => $inProgressCount,
                'draft_count' => $draftCount,
                'compliance_rate' => $complianceRate,
            ],
            'nextCode' => $nextCode,
            'filters' => [
                'q' => $request->query('q', ''),
                'status' => $request->query('status', 'all'),
            ],
            'breadcrumbs' => [
                ['name' => 'Launcher', 'url' => '/'],
                ['name' => ucwords(str_replace('-', ' ', $moduleSlug)), 'url' => "/{$moduleSlug}"],
                ['name' => $currentMenu['category'] ?? 'Operasional', 'url' => '#'],
                ['name' => $currentMenu['name'], 'url' => $path],
            ],
        ]);
    }

    /**
     * Store a new feature record.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'feature_route' => 'required|string|max:191',
            'record_code' => 'nullable|string|max:64',
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'pic_name' => 'nullable|string|max:150',
            'entry_date' => 'nullable|date',
            'status' => 'required|string|in:Draft,In Progress,Pending Review,Approved,Rejected',
            'amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'custom_attributes' => 'nullable|array',
        ]);

        $route = $validated['feature_route'];
        $segments = explode('/', trim($route, '/'));
        $moduleSlug = $segments[0] ?? 'erp';
        if ($moduleSlug === 'produksi') {
            $moduleSlug = 'production';
        }

        $code = ! empty($validated['record_code'])
            ? trim($validated['record_code'])
            : ErpFeatureRecord::generateNextCode($moduleSlug, $route);

        $record = DB::transaction(function () use ($validated, $moduleSlug, $route, $code, $request) {
            return ErpFeatureRecord::create([
                'module_slug' => $moduleSlug,
                'feature_route' => $route,
                'record_code' => $code,
                'title' => $validated['title'],
                'category' => $validated['category'] ?? 'Operasional',
                'pic_name' => $validated['pic_name'] ?? ($request->user()?->name ?? 'Administrator'),
                'entry_date' => $validated['entry_date'] ?? now()->toDateString(),
                'status' => $validated['status'],
                'amount' => $validated['amount'] ?? null,
                'description' => $validated['description'] ?? null,
                'custom_attributes' => $validated['custom_attributes'] ?? null,
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);
        });

        return back()->with('success', "Data '{$record->title}' berhasil disimpan dengan nomor {$record->record_code}.");
    }

    /**
     * Update an existing feature record.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $record = ErpFeatureRecord::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'pic_name' => 'nullable|string|max:150',
            'entry_date' => 'nullable|date',
            'status' => 'required|string|in:Draft,In Progress,Pending Review,Approved,Rejected',
            'amount' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'custom_attributes' => 'nullable|array',
        ]);

        $record->update([
            'title' => $validated['title'],
            'category' => $validated['category'] ?? $record->category,
            'pic_name' => $validated['pic_name'] ?? $record->pic_name,
            'entry_date' => $validated['entry_date'] ?? $record->entry_date,
            'status' => $validated['status'],
            'amount' => $validated['amount'] ?? null,
            'description' => $validated['description'] ?? null,
            'custom_attributes' => $validated['custom_attributes'] ?? $record->custom_attributes,
            'updated_by' => $request->user()?->id,
        ]);

        return back()->with('success', "Data {$record->record_code} berhasil diperbarui.");
    }

    /**
     * Delete an existing feature record.
     */
    public function destroy(int $id): RedirectResponse
    {
        $record = ErpFeatureRecord::findOrFail($id);
        $code = $record->record_code;
        $record->delete();

        return back()->with('success', "Data {$code} berhasil dihapus dari sistem.");
    }

    /**
     * Export feature records to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $route = $request->query('route');
        if (! $route) {
            abort(400, 'Parameter route wajib disertakan.');
        }

        $query = ErpFeatureRecord::where('feature_route', $route);

        if ($search = $request->query('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('record_code', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        $records = $query->orderBy('id')->get();
        $cleanName = trim(str_replace(['/', '-'], ['_', '_'], $route), '_');
        $fileName = "ekspor_{$cleanName}_".date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($handle, [
                'No',
                'Nomor Kode',
                'Judul / Nama Entri',
                'Kategori',
                'PIC / Pemohon',
                'Tanggal',
                'Status',
                'Nilai Nominal (Rp)',
                'Deskripsi / Catatan',
            ]);

            foreach ($records as $index => $row) {
                fputcsv($handle, [
                    $index + 1,
                    $row->record_code,
                    $row->title,
                    $row->category,
                    $row->pic_name ?? '-',
                    $row->entry_date ? $row->entry_date->format('Y-m-d') : '-',
                    $row->status,
                    $row->amount ? number_format((float) $row->amount, 2, ',', '.') : '0,00',
                    $row->description ?? '-',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Seed initial realistic records if empty for a feature route.
     */
    protected function ensureInitialRecords(string $moduleSlug, string $featureRoute, array $feature): void
    {
        $exists = ErpFeatureRecord::where('feature_route', $featureRoute)->exists();
        if ($exists) {
            return;
        }

        $featureName = $feature['name'] ?? 'Entri Dokumen';
        $category = $feature['category'] ?? 'Operasional';

        $statuses = ['Approved', 'Approved', 'In Progress', 'Pending Review', 'Draft'];
        $pics = [
            'Budi Pratama (SPV)',
            'Siti Rahmawati, S.Si (Lead)',
            'Aditya Pratama (Officer)',
            'Dewi Lestari (QA Specialist)',
            'Rian Firmansyah (Teknis)',
        ];

        $notes = [
            'Dokumen telah diverifikasi dan memenuhi standar SOP korporasi.',
            'Menunggu persetujuan lanjutan dari kepala departemen terkait.',
            'Dalam proses pengerjaan lapangan dan koordinasi tim teknis.',
            'Kalkulasi awal dan penyesuaian parameter terlampir.',
            'Draf awal dibuat untuk telaah berkala audit internal.',
        ];

        for ($i = 1; $i <= 5; $i++) {
            $code = ErpFeatureRecord::generateNextCode($moduleSlug, $featureRoute);
            ErpFeatureRecord::create([
                'module_slug' => $moduleSlug,
                'feature_route' => $featureRoute,
                'record_code' => $code,
                'title' => "{$featureName} Batch #".str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'category' => $category,
                'pic_name' => $pics[($i - 1) % count($pics)],
                'entry_date' => now()->subDays(6 - $i)->toDateString(),
                'status' => $statuses[($i - 1) % count($statuses)],
                'amount' => ($i * 1500000) + 2500000,
                'description' => $notes[($i - 1) % count($notes)],
                'custom_attributes' => [
                    'source_system' => 'HerbaTech Standalone ERP',
                    'compliance_check' => 'PASS',
                ],
                'created_by' => 1,
                'updated_by' => 1,
            ]);
        }
    }
}
