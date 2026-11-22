<?php

namespace App\Modules\Procurement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrItemDelivery extends Model
{
    use HasFactory;

    protected $table = 'pr_item_deliveries';

    protected $guarded = ['id'];

    protected $casts = [
        'delivery_date' => 'date',
        'delivered_quantity' => 'decimal:2',
        'is_rejected' => 'boolean',
        'rejected_quantity' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(PrItem::class, 'pr_item_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function originalDelivery(): BelongsTo
    {
        return $this->belongsTo(self::class, 'retur_for_delivery_id');
    }

    public function returReceipts(): HasMany
    {
        return $this->hasMany(self::class, 'retur_for_delivery_id');
    }
}
