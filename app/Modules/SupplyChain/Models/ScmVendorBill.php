<?php

namespace App\Modules\SupplyChain\Models;

use App\Core\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScmVendorBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'scm_vendor_bills';

    protected $guarded = ['id'];

    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'custom_attributes' => 'array',
    ];

    public function threeWayMatching(): BelongsTo
    {
        return $this->belongsTo(ScmThreeWayMatching::class, 'three_way_matching_id');
    }

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(ScmGoodsReceipt::class, 'goods_receipt_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
