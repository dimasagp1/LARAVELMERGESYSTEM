<?php

namespace App\Modules\SupplyChain\Models;

use App\Core\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScmMaterialReturn extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'scm_material_returns';

    protected $guarded = ['id'];

    protected $casts = [
        'return_date' => 'date',
        'total_amount' => 'decimal:2',
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ScmMaterialReturnItem::class, 'material_return_id');
    }
}
