<?php

namespace App\Modules\Procurement\Models;

use App\Core\Models\Product;
use App\Core\Models\Vendor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PrItem extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'pr_items';

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'ready_quantity' => 'decimal:2',
        'estimated_price' => 'decimal:2',
        'actual_price' => 'decimal:2',
        'rekap_po_odoo' => 'boolean',
        'is_incoming' => 'boolean',
        'need_holding_quotation' => 'boolean',
        'need_holding_delivery' => 'boolean',
        'is_bon' => 'boolean',
        'bon_unit_price' => 'decimal:2',
        'po_manual_odoo' => 'boolean',
        'po_manual_at' => 'datetime',
        'custom_attributes' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('proc_pr_item');
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function bon(): BelongsTo
    {
        return $this->belongsTo(PrBon::class, 'pr_bon_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function manualOdooUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'po_manual_by');
    }

    public function deliveryPlans(): HasMany
    {
        return $this->hasMany(PrItemDeliveryPlan::class, 'pr_item_id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(PrItemDelivery::class, 'pr_item_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PrApproval::class, 'pr_item_id');
    }

    public function isDelivered(): bool
    {
        return $this->ready_quantity >= $this->quantity && $this->quantity > 0;
    }

    public function pendingQuantity(): float
    {
        return max(0, (float) $this->quantity - (float) $this->ready_quantity);
    }
}
