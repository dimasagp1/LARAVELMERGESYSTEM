<?php

namespace App\Modules\GeneralAffair\Controllers;

use App\Core\Models\Department;
use App\Core\Models\Location;
use App\Core\Models\Plant;
use App\Core\Services\ModuleManagerService;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\GeneralAffair\Models\AssetCategory;
use App\Modules\GeneralAffair\Models\GaAsset;
use App\Modules\GeneralAffair\Services\FixedAssetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class FixedAssetController extends Controller
{
    public function __construct(
        protected FixedAssetService $assetService,
        protected ModuleManagerService $moduleManager
    ) {}

    /**
     * Display list of fixed assets.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'plant_id', 'department_id', 'category_id', 'condition']);
        $assets = $this->assetService->getAssets($filters, 15);

        $plants = Plant::where('is_active', true)->get(['id', 'name', 'code']);
        $departments = Department::where('is_active', true)->get(['id', 'name']);
        $categories = AssetCategory::where('is_active', true)->get(['id', 'name', 'code']);

        $allActive = GaAsset::where('condition', '!=', 'DISPOSED')->get();
        $totalCost = $allActive->sum('purchase_cost');
        $totalBookValue = $allActive->sum('book_value');
        $totalDepreciation = $allActive->sum('accumulated_depreciation');

        return Inertia::render('Modules/GeneralAffair/Assets/Index', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('general-affair'),
            ],
            'assets' => $assets,
            'filters' => $filters,
            'plants' => $plants,
            'departments' => $departments,
            'categories' => $categories,
            'stats' => [
                'total_assets' => $allActive->count(),
                'total_acquisition_cost' => (float) $totalCost,
                'total_accumulated_depreciation' => (float) $totalDepreciation,
                'total_book_value' => (float) $totalBookValue,
            ],
        ]);
    }

    /**
     * Show form to register a new fixed asset.
     */
    public function create(): Response
    {
        $plants = Plant::where('is_active', true)->get(['id', 'name', 'code']);
        $departments = Department::where('is_active', true)->get(['id', 'name']);
        $locations = Location::where('is_active', true)->get(['id', 'name', 'plant_id', 'department_id']);
        $categories = AssetCategory::where('is_active', true)->get(['id', 'name', 'code']);

        return Inertia::render('Modules/GeneralAffair/Assets/Form', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('general-affair'),
            ],
            'plants' => $plants,
            'departments' => $departments,
            'locations' => $locations,
            'categories' => $categories,
            'conditions' => ['BAIK', 'RUSAK_RINGAN', 'RUSAK_BERAT'],
        ]);
    }

    /**
     * Store new fixed asset.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|min:3',
            'plant_id' => 'required|exists:plants,id',
            'department_id' => 'required|exists:departments,id',
            'location_id' => 'nullable|exists:locations,id',
            'category_id' => 'required|exists:ga_asset_categories,id',
            'classification' => 'required|string',
            'purchase_date' => 'required|date',
            'purchase_cost' => 'required|numeric|min:0',
            'useful_life_years' => 'required|integer|min:1',
            'condition' => 'required|in:BAIK,RUSAK_RINGAN,RUSAK_BERAT,Baik,Rusak Ringan,Rusak Berat/Perbaikan,DISPOSED',
            'pic_name' => 'nullable|string',
            'short_name' => 'nullable|string|max:10',
            'notes' => 'nullable|string',
            'custom_attributes' => 'nullable|array',
        ]);

        $asset = $this->assetService->createAsset($validated);

        return redirect()->route('general-affair.assets.show', $asset->id)
            ->with('success', "Aset tetap {$asset->asset_code} ({$asset->name}) berhasil didaftarkan.");
    }

    /**
     * Show asset detail with depreciation and mutation logs.
     */
    public function show(int $id): Response
    {
        $asset = GaAsset::with([
            'plant',
            'department',
            'location',
            'category',
            'mutations.fromPlant',
            'mutations.toPlant',
            'mutations.fromDepartment',
            'mutations.toDepartment',
            'mutations.fromLocation',
            'mutations.toLocation',
            'mutations.transferredByUser',
        ])->findOrFail($id);

        $plants = Plant::where('is_active', true)->get(['id', 'name', 'code']);
        $departments = Department::where('is_active', true)->get(['id', 'name']);
        $locations = Location::where('is_active', true)->get(['id', 'name', 'plant_id', 'department_id']);

        return Inertia::render('Modules/GeneralAffair/Assets/Show', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('general-affair'),
            ],
            'asset' => $asset,
            'plants' => $plants,
            'departments' => $departments,
            'locations' => $locations,
        ]);
    }

    /**
     * Mutate asset to new plant/department/location.
     */
    public function mutate(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'to_plant_id' => 'required|exists:plants,id',
            'to_department_id' => 'required|exists:departments,id',
            'to_location_id' => 'nullable|exists:locations,id',
            'pic_receiver' => 'required|string|min:2',
            'reason' => 'required|string|min:5',
            'mutation_date' => 'nullable|date',
        ]);

        $asset = GaAsset::findOrFail($id);
        $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();

        $this->assetService->mutateAsset($asset, $validated, $user);

        return back()->with('success', "Mutasi aset {$asset->asset_code} berhasil diproses.");
    }

    /**
     * Dispose / write-off asset.
     */
    public function dispose(int $id, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|min:5',
            'scrap_value' => 'nullable|numeric|min:0',
        ]);

        $asset = GaAsset::findOrFail($id);
        $user = $request->user() ?? User::where('email', 'admin@herbatech.com')->first();

        $this->assetService->disposeAsset($asset, $validated['reason'], (float) ($validated['scrap_value'] ?? 0), $user);

        return back()->with('success', "Aset {$asset->asset_code} berhasil dihapuskan (Disposed).");
    }

    /**
     * Display Daftar Inventaris Ruangan (DIR) - Lampiran C.
     */
    public function dir(Request $request): Response
    {
        $locations = Location::with('plant')->where('is_active', true)->get();
        $selectedLocationId = (int) $request->query('location_id', $locations->first()?->id ?? 0);

        $dirReport = null;
        if ($selectedLocationId > 0) {
            $dirReport = $this->assetService->getDirReport($selectedLocationId);
        }

        return Inertia::render('Modules/GeneralAffair/Assets/Dir', [
            'module' => [
                'name' => 'General Affair (FPUGA)',
                'slug' => 'general-affair',
                'color_theme' => 'amber',
                'menus' => $this->moduleManager->getModuleMenus('general-affair'),
            ],
            'locations' => $locations,
            'selectedLocationId' => $selectedLocationId,
            'report' => $dirReport,
        ]);
    }

    /**
     * Export DIR Lampiran C PDF.
     */
    public function printDirPdf(int $locationId): HttpResponse
    {
        $dirReport = $this->assetService->getDirReport($locationId);

        $pdf = Pdf::loadView('pdf.ga_dir_lampiran_c', [
            'report' => $dirReport,
            'companyName' => 'PT HerbaTech Indo Utama',
            'printedAt' => now()->format('d/m/Y H:i:s'),
        ]);

        $locationName = str_replace([' ', '/', '\\'], '-', $dirReport['location']->name);

        return $pdf->stream("DIR-Lampiran-C-{$locationName}.pdf");
    }
}
