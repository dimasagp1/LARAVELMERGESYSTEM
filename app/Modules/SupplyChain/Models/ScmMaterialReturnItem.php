<?php

namespace App\Modules\SupplyChain\Models;

use App\Core\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScmMaterialReturnItem extends Model
{
    use HasFactory;

    protected $table = 'scm_material_return_items';

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function materialReturn(): BelongsTo
    {
        return $this->belongsTo(ScmMaterialReturn::class, 'material_return_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
