<?php

namespace App\Modules\SupplyChain\Models;

use App\Core\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScmQcInspection extends Model
{
    use HasFactory;

    protected $table = 'scm_qc_inspections';

    protected $guarded = ['id'];

    protected $casts = [
        'inspection_date' => 'date',
        'sample_qty' => 'decimal:2',
        'passed_qty' => 'decimal:2',
        'rejected_qty' => 'decimal:2',
        'is_visual_passed' => 'boolean',
        'is_lab_passed' => 'boolean',
        'is_quantity_passed' => 'boolean',
        'is_docs_passed' => 'boolean',
        'custom_attributes' => 'array',
    ];

    public function goodsReceiptItem(): BelongsTo
    {
        return $this->belongsTo(ScmGoodsReceiptItem::class, 'goods_receipt_item_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function isFullyPassed(): bool
    {
        return $this->is_visual_passed &&
               $this->is_lab_passed &&
               $this->is_quantity_passed &&
               $this->is_docs_passed &&
               $this->rejected_qty <= 0;
    }
}
