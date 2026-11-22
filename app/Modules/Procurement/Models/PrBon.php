<?php

namespace App\Modules\Procurement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PrBon extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'pr_bons';

    protected $guarded = ['id'];

    protected $casts = [
        'bon_date' => 'date',
        'total_amount' => 'decimal:2',
        'approved_at' => 'datetime',
        'revised_items' => 'array',
        'custom_attributes' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('proc_pr_bon');
    }

    public function purchaseRequest(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrItem::class, 'pr_bon_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function recalculateTotal(): void
    {
        $this->total_amount = $this->items()->sum('bon_unit_price') ?: $this->items()->sum('actual_price');
        $this->saveQuietly();
    }
}
