<?php

namespace App\Modules\Hris\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrisLeave extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hris_leaves';

    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'custom_attributes' => 'array',
    ];

    protected $appends = [
        'emergency_phone',
        'substitute_employee_id',
        'rejection_reason',
        'spv_approval_id',
        'spv_approved_at',
        'hr_approval_id',
        'hr_approved_at',
    ];

    public function getEmergencyPhoneAttribute(): ?string
    {
        return $this->custom_attributes['emergency_phone'] ?? null;
    }

    public function setEmergencyPhoneAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['emergency_phone'] = $value;
        $this->custom_attributes = $attrs;
    }

    public function getSubstituteEmployeeIdAttribute(): ?int
    {
        return isset($this->custom_attributes['substitute_employee_id']) ? (int) $this->custom_attributes['substitute_employee_id'] : null;
    }

    public function setSubstituteEmployeeIdAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['substitute_employee_id'] = $value ? (int) $value : null;
        $this->custom_attributes = $attrs;
    }

    public function getRejectionReasonAttribute(): ?string
    {
        return $this->custom_attributes['rejection_reason'] ?? null;
    }

    public function setRejectionReasonAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['rejection_reason'] = $value;
        $this->custom_attributes = $attrs;
    }

    public function getSpvApprovalIdAttribute(): ?int
    {
        return isset($this->custom_attributes['spv_approval_id']) ? (int) $this->custom_attributes['spv_approval_id'] : null;
    }

    public function setSpvApprovalIdAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['spv_approval_id'] = $value;
        $this->custom_attributes = $attrs;
    }

    public function getSpvApprovedAtAttribute(): ?string
    {
        return $this->custom_attributes['spv_approved_at'] ?? null;
    }

    public function setSpvApprovedAtAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['spv_approved_at'] = $value;
        $this->custom_attributes = $attrs;
    }

    public function getHrApprovalIdAttribute(): ?int
    {
        return isset($this->custom_attributes['hr_approval_id']) ? (int) $this->custom_attributes['hr_approval_id'] : null;
    }

    public function setHrApprovalIdAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['hr_approval_id'] = $value;
        $this->custom_attributes = $attrs;
    }

    public function getHrApprovedAtAttribute(): ?string
    {
        return $this->custom_attributes['hr_approved_at'] ?? null;
    }

    public function setHrApprovedAtAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['hr_approved_at'] = $value;
        $this->custom_attributes = $attrs;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrisEmployee::class, 'employee_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(HrisLeaveCategory::class, 'leave_category_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function spvApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function hrApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function substituteEmployee(): BelongsTo
    {
        return $this->belongsTo(HrisEmployee::class, 'substitute_employee_id');
    }
}
