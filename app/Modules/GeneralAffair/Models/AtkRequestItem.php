<?php

namespace App\Modules\GeneralAffair\Models;

use App\Core\Models\Uom;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AtkRequestItem extends Model
{
    use HasFactory;

    protected $table = 'ga_atk_request_items';

    protected $fillable = [
        'request_id',
        'atk_item_id',
        'custom_item_name',
        'quantity',
        'approved_quantity',
        'uom_id',
        'status',
        'rejection_note',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'approved_quantity' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<AtkRequest, $this>
     */
    public function request(): BelongsTo
    {
        return $this->belongsTo(AtkRequest::class, 'request_id');
    }

    /**
     * @return BelongsTo<AtkItem, $this>
     */
    public function atkItem(): BelongsTo
    {
        return $this->belongsTo(AtkItem::class, 'atk_item_id');
    }

    /**
     * @return BelongsTo<Uom, $this>
     */
    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'uom_id');
    }

    public function getItemDisplayNameAttribute(): string
    {
        return $this->atkItem?->name ?? ($this->custom_item_name ?? 'Barang Kustom');
    }
}
