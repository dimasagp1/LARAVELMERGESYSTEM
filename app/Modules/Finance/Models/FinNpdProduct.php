<?php

namespace App\Modules\Finance\Models;

use App\Core\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinNpdProduct extends Model
{
    use HasFactory;

    protected $table = 'fin_npd_products';

    protected $guarded = ['id'];

    protected $casts = [
        'launch_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function isWithinActiveWindow(?string $date = null): bool
    {
        $date = $date ? Carbon::parse($date) : Carbon::now();

        return $this->launch_date && $date->diffInDays($this->launch_date) <= 365;
    }
}
