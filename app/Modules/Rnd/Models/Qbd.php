<?php

namespace App\Modules\Rnd\Models;

use App\Core\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Qbd extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'rnd_qbds';

    protected $guarded = ['id'];

    protected $casts = [
        'qtpp_json' => 'array',
        'cqa_json' => 'array',
        'cma_json' => 'array',
        'cpp_json' => 'array',
        'risk_analysis_json' => 'array',
        'custom_attributes' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('rnd_qbd');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
