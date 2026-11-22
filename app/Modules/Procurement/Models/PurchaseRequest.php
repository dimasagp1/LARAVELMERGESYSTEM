<?php

namespace App\Modules\Procurement\Models;

use App\Core\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseRequest extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'purchase_requests';

    protected $guarded = ['id'];

    protected $casts = [
        'request_date' => 'date',
        'total_amount' => 'decimal:2',
        'bypass_budget_check' => 'boolean',
        'revision_count' => 'integer',
        'custom_attributes' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->useLogName('proc_purchase_request');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function parentPr(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_pr_id');
    }

    public function childPrs(): HasMany
    {
        return $this->hasMany(self::class, 'parent_pr_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrItem::class, 'purchase_request_id');
    }

    public function bons(): HasMany
    {
        return $this->hasMany(PrBon::class, 'purchase_request_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(PrApproval::class, 'purchase_request_id');
    }

    public function expenseStagings(): HasMany
    {
        return $this->hasMany(ExpenseStaging::class, 'purchase_request_id');
    }

    public function recalculateTotal(): void
    {
        $this->total_amount = $this->items()->sum('actual_price') ?: $this->items()->sum('estimated_price');
        $this->saveQuietly();
    }
}
