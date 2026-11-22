<?php

namespace App\Modules\SupplyChain\Models;

use App\Core\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScmForecast extends Model
{
    use HasFactory;

    protected $table = 'scm_forecasts';

    protected $guarded = ['id'];

    protected $casts = [
        'period_year' => 'integer',
        'period_month' => 'integer',
        'forecast_quantity' => 'decimal:2',
        'forecast_value' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
