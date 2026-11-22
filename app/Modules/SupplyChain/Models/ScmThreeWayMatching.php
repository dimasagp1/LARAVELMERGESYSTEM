<?php

namespace App\Modules\SupplyChain\Models;

use App\Core\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ScmThreeWayMatching extends Model
{
    use HasFactory;

    protected $table = 'scm_three_way_matchings';

    protected $guarded = ['id'];

    protected $casts = [
        'po_total_qty' => 'decimal:2',
        'po_unit_price' => 'decimal:2',
        'gr_passed_qty' => 'decimal:2',
        'invoice_qty' => 'decimal:2',
        'invoice_amount' => 'decimal:2',
        'discrepancy_qty' => 'decimal:2',
        'discrepancy_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'custom_attributes' => 'array',
    ];

    public function goodsReceipt(): BelongsTo
    {
        return $this->belongsTo(ScmGoodsReceipt::class, 'goods_receipt_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'audited_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function vendorBill(): HasOne
    {
        return $this->hasOne(ScmVendorBill::class, 'three_way_matching_id');
    }

    public function isDiscrepant(): bool
    {
        return abs($this->discrepancy_qty) > 0.01 || abs($this->discrepancy_amount) > 0.01;
    }
}
