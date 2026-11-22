<?php

namespace App\Modules\GeneralAffair\Services;

use App\Core\Models\Department;
use App\Core\Models\Location;
use App\Core\Models\Plant;
use App\Models\User;
use App\Modules\GeneralAffair\Models\AssetCategory;
use App\Modules\GeneralAffair\Models\AssetMutation;
use App\Modules\GeneralAffair\Models\GaAsset;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FixedAssetService
{
    public function __construct(
        protected GaNumberingService $numberingService
    ) {}

    /**
     * Get paginated fixed assets with filtering.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<GaAsset>
     */
    public function getAssets(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = GaAsset::with(['plant', 'department', 'location', 'category', 'mutations'])
            ->orderByDesc('id');

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('pic_name', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['plant_id'])) {
            $query->where('plant_id', (int) $filters['plant_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', (int) $filters['department_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (! empty($filters['condition'])) {
            $query->where('condition', $filters['condition']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new fixed asset and auto-generate code.
     *
     * @param  array<string, mixed>  $data
     */
    public function createAsset(array $data): GaAsset
    {
        return DB::transaction(function () use ($data) {
            $plant = Plant::findOrFail($data['plant_id']);
            $department = Department::findOrFail($data['department_id']);
            $category = AssetCategory::findOrFail($data['category_id']);

            $shortName = $data['short_name'] ?? substr($data['name'], 0, 3);
            $assetCode = $this->numberingService->generateAssetCode($plant, $department, $category, $shortName);

            $classification = match (strtoupper($data['classification'] ?? 'UTAMA')) {
                'TERPISAH' => 'TERPISAH',
                'BERGERAK' => 'BERGERAK',
                default => 'UTAMA',
            };

            $condition = match (strtoupper(str_replace(' ', '_', $data['condition'] ?? 'BAIK'))) {
                'RUSAK_RINGAN' => 'Rusak Ringan',
                'RUSAK_BERAT', 'RUSAK_BERAT/PERBAIKAN' => 'Rusak Berat/Perbaikan',
                'DISPOSED' => 'DISPOSED',
                default => 'Baik',
            };

            $asset = GaAsset::create([
                'asset_code' => $assetCode,
                'name' => $data['name'],
                'plant_id' => $plant->id,
                'department_id' => $department->id,
                'location_id' => $data['location_id'] ?? null,
                'category_id' => $category->id,
                'classification' => $classification,
                'purchase_date' => $data['purchase_date'] ?? Carbon::now()->toDateString(),
                'purchase_cost' => (float) ($data['purchase_cost'] ?? 0),
                'useful_life_years' => (int) ($data['useful_life_years'] ?? 4),
                'condition' => $condition,
                'pic_name' => $data['pic_name'] ?? null,
                'barcode_qr' => $assetCode,
                'notes' => $data['notes'] ?? null,
                'custom_attributes' => $data['custom_attributes'] ?? null,
            ]);

            return $asset->load(['plant', 'department', 'location', 'category']);
        });
    }

    /**
     * Mutate asset to new plant, department, or location.
     *
     * @param  array<string, mixed>  $data
     */
    public function mutateAsset(GaAsset $asset, array $data, User $actor): AssetMutation
    {
        return DB::transaction(function () use ($asset, $data, $actor) {
            $oldPlantId = $asset->plant_id;
            $oldDeptId = $asset->department_id;
            $oldLocId = $asset->location_id;

            $newPlantId = ! empty($data['to_plant_id']) ? (int) $data['to_plant_id'] : $oldPlantId;
            $newDeptId = ! empty($data['to_department_id']) ? (int) $data['to_department_id'] : $oldDeptId;
            $newLocId = ! empty($data['to_location_id']) ? (int) $data['to_location_id'] : $oldLocId;
            $picReceiver = $data['pic_receiver'] ?? $asset->pic_name;

            // Create mutation record
            $mutation = AssetMutation::create([
                'asset_id' => $asset->id,
                'mutation_date' => $data['mutation_date'] ?? Carbon::now()->toDateString(),
                'from_plant_id' => $oldPlantId,
                'to_plant_id' => $newPlantId,
                'from_department_id' => $oldDeptId,
                'to_department_id' => $newDeptId,
                'from_location_id' => $oldLocId,
                'to_location_id' => $newLocId,
                'pic_receiver' => $picReceiver,
                'reason' => $data['reason'] ?? 'Mutasi internal aset operasional',
                'transferred_by_user_id' => $actor->id,
            ]);

            // Update asset position
            $asset->update([
                'plant_id' => $newPlantId,
                'department_id' => $newDeptId,
                'location_id' => $newLocId,
                'pic_name' => $picReceiver,
            ]);

            return $mutation->load(['fromPlant', 'toPlant', 'fromDepartment', 'toDepartment', 'fromLocation', 'toLocation']);
        });
    }

    /**
     * Dispose / write-off asset.
     */
    public function disposeAsset(GaAsset $asset, string $reason, ?float $scrapValue = null, ?User $actor = null): GaAsset
    {
        return DB::transaction(function () use ($asset, $reason, $scrapValue) {
            $notes = trim($asset->notes."\n[DISPOSED pada ".Carbon::now()->toDateTimeString().']: '.$reason.($scrapValue ? ' Nilai Sisa: Rp '.number_format($scrapValue, 2) : ''));

            $asset->update([
                'condition' => 'DISPOSED',
                'notes' => $notes,
            ]);

            return $asset->fresh(['plant', 'department', 'location', 'category']);
        });
    }

    /**
     * Retrieve DIR (Daftar Inventaris Ruangan - Lampiran C) for a given location.
     *
     * @return array<string, mixed>
     */
    public function getDirReport(int $locationId): array
    {
        $location = Location::with('plant')->findOrFail($locationId);
        $assets = GaAsset::with(['category', 'plant', 'department'])
            ->where('location_id', $locationId)
            ->where('condition', '!=', 'DISPOSED')
            ->orderBy('name')
            ->get();

        $totalCost = $assets->sum('purchase_cost');
        $totalBookValue = $assets->sum('book_value');
        $totalDepreciation = $assets->sum('accumulated_depreciation');

        return [
            'location' => $location,
            'assets' => $assets,
            'summary' => [
                'total_items' => $assets->count(),
                'total_acquisition_cost' => (float) $totalCost,
                'total_accumulated_depreciation' => (float) $totalDepreciation,
                'total_book_value' => (float) $totalBookValue,
            ],
        ];
    }
}
