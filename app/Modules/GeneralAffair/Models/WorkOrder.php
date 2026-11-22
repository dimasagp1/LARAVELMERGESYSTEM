<?php

namespace App\Modules\GeneralAffair\Models;

use App\Core\Models\Department;
use App\Core\Models\Location;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ga_work_orders';

    protected $fillable = [
        'wo_number',
        'user_id',
        'department_id',
        'location_id',
        'complaint_description',
        'priority',
        'category',
        'attachment_path',
        'assigned_engineer_id',
        'assigned_at',
        'check_result',
        'engineer_needs',
        'needs_material',
        'rejection_note',
        'status',
        'custom_attributes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'needs_material' => 'boolean',
            'custom_attributes' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Department, $this>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * @return BelongsTo<Location, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function assignedEngineer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_engineer_id');
    }

    /**
     * @return HasMany<WorkOrderProgress, $this>
     */
    public function progresses(): HasMany
    {
        return $this->hasMany(WorkOrderProgress::class, 'work_order_id')->latest('created_at');
    }

    /**
     * @return HasOne<FpuRequest, $this>
     */
    public function fpuRequest(): HasOne
    {
        return $this->hasOne(FpuRequest::class, 'work_order_id');
    }

    /**
     * @return HasOne<WoHandover, $this>
     */
    public function handover(): HasOne
    {
        return $this->hasOne(WoHandover::class, 'work_order_id');
    }

    public function getCurrentProgressPercentAttribute(): int
    {
        return $this->progresses()->first()?->progress_percent ?? ($this->status === 'Selesai' ? 100 : 0);
    }
}
