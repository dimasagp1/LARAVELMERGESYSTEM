<?php

namespace App\Modules\SupplyChain\Models;

use App\Core\Models\Plant;
use App\Core\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScmGoodsReceipt extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'scm_goods_receipts';

    protected $guarded = ['id'];

    protected $casts = [
        'receipt_date' => 'date',
        'custom_attributes' => 'array',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ScmGoodsReceiptItem::class, 'goods_receipt_id');
    }
}
