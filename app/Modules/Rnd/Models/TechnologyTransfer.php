<?php

namespace App\Modules\Rnd\Models;

use App\Core\Models\Plant;
use App\Core\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TechnologyTransfer extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'rnd_technology_transfers';

    protected $guarded = ['id'];

    protected $casts = [
        'scale_up_batch_size' => 'decimal:2',
        'custom_attributes' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('rnd_technology_transfer');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(Formula::class, 'formula_id');
    }

    public function targetPlant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'target_plant_id');
    }
}
