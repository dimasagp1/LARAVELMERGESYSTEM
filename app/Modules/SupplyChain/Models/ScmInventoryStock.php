<?php

namespace App\Modules\SupplyChain\Models;

use App\Core\Models\Location;
use App\Core\Models\Plant;
use App\Core\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScmInventoryStock extends Model
{
    use HasFactory;

    protected $table = 'scm_inventory_stocks';

    protected $guarded = ['id'];

    protected $casts = [
        'expiry_date' => 'date',
        'quantity_on_hand' => 'decimal:2',
        'quantity_reserved' => 'decimal:2',
        'quantity_available' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'custom_attributes' => 'array',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function isNearExpiry(int $daysThreshold = 90): bool
    {
        if (! $this->expiry_date) {
            return false;
        }

        return $this->expiry_date->diffInDays(now(), false) >= -$daysThreshold;
    }
}
