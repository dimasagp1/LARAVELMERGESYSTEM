<?php

namespace App\Modules\GeneralAffair\Models;

use App\Core\Models\Uom;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtkItem extends Model
{
    use HasFactory;

    protected $table = 'ga_atk_items';

    protected $fillable = [
        'category_id',
        'item_code',
        'name',
        'uom_id',
        'min_stock',
        'current_stock',
        'standard_cost',
        'is_active',
    ];

    protected $appends = [
        'stock_actual',
        'stock_minimum',
    ];

    protected function casts(): array
    {
        return [
            'standard_cost' => 'decimal:2',
            'is_active' => 'boolean',
            'min_stock' => 'integer',
            'current_stock' => 'integer',
        ];
    }

    public function getStockActualAttribute(): int
    {
        return $this->current_stock ?? 0;
    }

    public function setStockActualAttribute($value): void
    {
        $this->attributes['current_stock'] = (int) $value;
    }

    public function getStockMinimumAttribute(): int
    {
        return $this->min_stock ?? 0;
    }

    public function setStockMinimumAttribute($value): void
    {
        $this->attributes['min_stock'] = (int) $value;
    }

    /**
     * @return BelongsTo<AtkCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AtkCategory::class, 'category_id');
    }

    /**
     * @return BelongsTo<Uom, $this>
     */
    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }
}
