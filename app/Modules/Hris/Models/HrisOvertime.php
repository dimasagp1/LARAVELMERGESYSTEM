<?php

namespace App\Modules\Hris\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrisOvertime extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hris_overtimes';

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'total_hours' => 'decimal:2',
        'calculated_hours' => 'decimal:2',
        'approved_at' => 'datetime',
        'custom_attributes' => 'array',
    ];

    protected $appends = [
        'multiplier_hours',
        'rate_per_hour',
        'total_overtime_pay',
        'work_target',
        'is_holiday',
    ];

    public function getCompensationTypeAttribute(): string
    {
        $val = $this->attributes['compensation_type'] ?? 'paid';

        return $val === 'paid' ? 'cash' : 'time_bank';
    }

    public function setCompensationTypeAttribute($value): void
    {
        $this->attributes['compensation_type'] = ($value === 'cash' || $value === 'paid') ? 'paid' : 'timebank';
    }

    public function getMultiplierHoursAttribute(): float
    {
        return (float) ($this->calculated_hours ?? $this->total_hours);
    }

    public function setMultiplierHoursAttribute($value): void
    {
        $this->attributes['calculated_hours'] = $value;
    }

    public function getRatePerHourAttribute(): float
    {
        return (float) ($this->custom_attributes['rate_per_hour'] ?? 0);
    }

    public function setRatePerHourAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['rate_per_hour'] = $value;
        $this->custom_attributes = $attrs;
    }

    public function getTotalOvertimePayAttribute(): float
    {
        return (float) ($this->custom_attributes['total_overtime_pay'] ?? 0);
    }

    public function setTotalOvertimePayAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['total_overtime_pay'] = $value;
        $this->custom_attributes = $attrs;
    }

    public function getWorkTargetAttribute(): ?string
    {
        return $this->custom_attributes['work_target'] ?? null;
    }

    public function setWorkTargetAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['work_target'] = $value;
        $this->custom_attributes = $attrs;
    }

    public function getIsHolidayAttribute(): bool
    {
        return (bool) ($this->custom_attributes['is_holiday'] ?? false);
    }

    public function setIsHolidayAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['is_holiday'] = (bool) $value;
        $this->custom_attributes = $attrs;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrisEmployee::class, 'employee_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function operationsManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function generalManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
