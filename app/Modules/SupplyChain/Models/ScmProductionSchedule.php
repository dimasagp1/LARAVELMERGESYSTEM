<?php

namespace App\Modules\SupplyChain\Models;

use App\Core\Models\Bom;
use App\Core\Models\Plant;
use App\Core\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScmProductionSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'scm_production_schedules';

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'planned_quantity' => 'decimal:2',
        'produced_quantity' => 'decimal:2',
        'custom_attributes' => 'array',
    ];

    protected $appends = ['schedule_date'];

    public function getScheduleDateAttribute(): string
    {
        return $this->start_date ? $this->start_date->format('Y-m-d') : '';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function bom(): BelongsTo
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
