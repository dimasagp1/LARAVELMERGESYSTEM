<?php

namespace App\Modules\Procurement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrItemDeliveryPlan extends Model
{
    use HasFactory;

    protected $table = 'pr_item_delivery_plans';

    protected $guarded = ['id'];

    protected $casts = [
        'plan_date' => 'date',
        'plan_quantity' => 'decimal:2',
        'is_active' => 'boolean',
        'is_rescheduled' => 'boolean',
        'original_plan_date' => 'date',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(PrItem::class, 'pr_item_id');
    }
}
