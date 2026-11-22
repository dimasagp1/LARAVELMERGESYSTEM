<?php

namespace App\Modules\Rnd\Models;

use App\Core\Models\Product;
use App\Core\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RndPriceStaging extends Model
{
    use HasFactory;

    protected $table = 'rnd_price_stagings';

    protected $fillable = [
        'product_id',
        'vendor_id',
        'source_type',
        'source_id',
        'po_number',
        'old_price',
        'new_price',
        'price_difference',
        'percentage_change',
        'status',
        'effective_date',
        'created_by',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_price' => 'decimal:2',
            'new_price' => 'decimal:2',
            'price_difference' => 'decimal:2',
            'percentage_change' => 'decimal:2',
            'effective_date' => 'date',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
