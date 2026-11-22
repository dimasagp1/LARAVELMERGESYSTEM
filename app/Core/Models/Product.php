<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Class Product
 *
 * @property int $id
 * @property string $sku
 * @property string $name
 * @property string $type
 * @property string|null $category
 * @property int $uom_id
 * @property int|null $secondary_uom_id
 * @property float $conversion_rate
 * @property float $min_stock
 * @property float $safety_stock
 * @property float $reorder_point
 * @property float $standard_cost
 * @property float $selling_price
 * @property string|null $description
 * @property array|null $specifications_json
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
class Product extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'sku',
        'name',
        'type',
        'category',
        'uom_id',
        'secondary_uom_id',
        'conversion_rate',
        'min_stock',
        'safety_stock',
        'reorder_point',
        'standard_cost',
        'selling_price',
        'description',
        'specifications_json',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'conversion_rate' => 'decimal:6',
            'min_stock' => 'decimal:4',
            'safety_stock' => 'decimal:4',
            'reorder_point' => 'decimal:4',
            'standard_cost' => 'decimal:4',
            'selling_price' => 'decimal:4',
            'specifications_json' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'sku',
                'name',
                'type',
                'category',
                'uom_id',
                'standard_cost',
                'selling_price',
                'is_active',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * @return BelongsTo<Uom, $this>
     */
    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }

    /**
     * @return BelongsTo<Uom, $this>
     */
    public function secondaryUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'secondary_uom_id');
    }

    /**
     * @return HasMany<Bom, $this>
     */
    public function boms(): HasMany
    {
        return $this->hasMany(Bom::class, 'product_id');
    }

    /**
     * @return HasMany<BomItem, $this>
     */
    public function bomItems(): HasMany
    {
        return $this->hasMany(BomItem::class, 'product_id');
    }
}
