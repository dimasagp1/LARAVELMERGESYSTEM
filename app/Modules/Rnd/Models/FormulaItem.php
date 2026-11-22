<?php

namespace App\Modules\Rnd\Models;

use App\Core\Models\Product;
use App\Core\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormulaItem extends Model
{
    use HasFactory;

    protected $table = 'rnd_formula_items';

    protected $guarded = ['id'];

    protected $casts = [
        'percentage' => 'decimal:4',
        'weight_per_serving_g' => 'decimal:4',
        'price_per_kg' => 'decimal:2',
        'price_per_serving' => 'decimal:4',
    ];

    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class, 'formula_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'material_product_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
