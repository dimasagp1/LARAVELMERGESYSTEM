<?php

namespace App\Modules\GeneralAffair\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FpuRequest extends Model
{
    use HasFactory;

    protected $table = 'ga_fpu_requests';

    protected $fillable = [
        'work_order_id',
        'fpu_number',
        'request_date',
        'priority',
        'execution_type',
        'category',
        'verified_by_hrga',
        'verified_by_user_id',
        'verified_at',
        'known_by_manager',
        'manager_user_id',
        'manager_approved_at',
        'approved_by_gm',
        'gm_user_id',
        'gm_approved_at',
        'status',
        'custom_attributes',
    ];

    protected function casts(): array
    {
        return [
            'request_date' => 'date',
            'verified_by_hrga' => 'boolean',
            'verified_at' => 'datetime',
            'known_by_manager' => 'boolean',
            'manager_approved_at' => 'datetime',
            'approved_by_gm' => 'boolean',
            'gm_approved_at' => 'datetime',
            'custom_attributes' => 'array',
        ];
    }

    /**
     * @return BelongsTo<WorkOrder, $this>
     */
    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id');
    }

    /**
     * @return HasMany<FpuMaterial, $this>
     */
    public function materials(): HasMany
    {
        return $this->hasMany(FpuMaterial::class, 'fpu_request_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function managerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function gmUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gm_user_id');
    }

    public function getTotalEstimatedCostAttribute(): float
    {
        return (float) $this->materials()->sum('estimated_cost');
    }
}
