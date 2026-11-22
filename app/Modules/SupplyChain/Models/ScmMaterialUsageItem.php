<?php

namespace App\Modules\SupplyChain\Models;

use App\Core\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScmMaterialUsageItem extends Model
{
    use HasFactory;

    protected $table = 'scm_material_usage_items';

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_cost' => 'decimal:2',
    ];

    public function materialUsage(): BelongsTo
    {
        return $this->belongsTo(ScmMaterialUsage::class, 'material_usage_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function inventoryStock(): BelongsTo
    {
        return $this->belongsTo(ScmInventoryStock::class, 'inventory_stock_id');
    }
}
