<?php

namespace App\Modules\Procurement\Models;

use App\Core\Models\Product;
use App\Core\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemStandardPrice extends Model
{
    use HasFactory;

    protected $table = 'item_standard_prices';

    protected $guarded = ['id'];

    protected $casts = [
        'standard_price' => 'decimal:2',
        'po_date' => 'datetime',
        'effective_year' => 'integer',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
}
