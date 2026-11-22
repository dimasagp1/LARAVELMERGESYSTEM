<?php

namespace App\Modules\SupplyChain\Models;

use App\Core\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ScmGoodsReceiptItem extends Model
{
    use HasFactory;

    protected $table = 'scm_goods_receipt_items';

    protected $guarded = ['id'];

    protected $casts = [
        'received_qty' => 'decimal:2',
        'passed_qty' => 'decimal:2',
        'rejected_qty' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(ScmGoodsReceipt::class, 'goods_receipt_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function inspection(): HasOne
    {
        return $this->hasOne(ScmQcInspection::class, 'goods_receipt_item_id');
    }
}
