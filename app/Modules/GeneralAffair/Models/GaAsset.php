<?php

namespace App\Modules\GeneralAffair\Models;

use App\Core\Models\Department;
use App\Core\Models\Location;
use App\Core\Models\Plant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class GaAsset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ga_assets';

    protected $fillable = [
        'asset_code',
        'name',
        'plant_id',
        'department_id',
        'location_id',
        'category_id',
        'classification',
        'purchase_date',
        'purchase_cost',
        'useful_life_years',
        'condition',
        'pic_name',
        'barcode_qr',
        'notes',
        'custom_attributes',
    ];

    protected $appends = [
        'monthly_depreciation',
        'months_used',
        'accumulated_depreciation',
        'book_value',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'purchase_cost' => 'decimal:2',
            'useful_life_years' => 'integer',
            'custom_attributes' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Plant, $this>
     */
    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<AssetCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    /**
     * @return HasMany<AssetMutation, $this>
     */
    public function mutations(): HasMany
    {
        return $this->hasMany(AssetMutation::class, 'asset_id')->latest('mutation_date');
    }

    /**
     * Monthly Depreciation = Purchase Cost / (Useful Life Years * 12).
     */
    public function getMonthlyDepreciationAttribute(): float
    {
        $totalMonths = max(1, ($this->useful_life_years ?? 4) * 12);

        return round((float) $this->purchase_cost / $totalMonths, 2);
    }

    /**
     * Months elapsed since purchase date.
     */
    public function getMonthsUsedAttribute(): int
    {
        if (! $this->purchase_date) {
            return 0;
        }

        $purchase = Carbon::parse($this->purchase_date);
        $now = Carbon::now();

        if ($now->lt($purchase)) {
            return 0;
        }

        return (int) $purchase->diffInMonths($now);
    }

    /**
     * Accumulated Depreciation = Monthly Depreciation * min(Months Used, Total Months).
     */
    public function getAccumulatedDepreciationAttribute(): float
    {
        $totalMonths = max(1, ($this->useful_life_years ?? 4) * 12);
        $monthsEffective = min($this->months_used, $totalMonths);

        return round($this->monthly_depreciation * $monthsEffective, 2);
    }

    /**
     * Net Book Value = max(0, Purchase Cost - Accumulated Depreciation).
     */
    public function getBookValueAttribute(): float
    {
        if ($this->condition === 'DISPOSED') {
            return 0.00;
        }

        return max(0.00, round((float) $this->purchase_cost - $this->accumulated_depreciation, 2));
    }
}
