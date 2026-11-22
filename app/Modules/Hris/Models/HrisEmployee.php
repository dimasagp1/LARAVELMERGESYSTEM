<?php

namespace App\Modules\Hris\Models;

use App\Core\Models\Department;
use App\Core\Models\Plant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrisEmployee extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hris_employees';

    protected $guarded = ['id'];

    protected $casts = [
        'join_date' => 'date',
        'basic_salary' => 'decimal:2',
        'custom_attributes' => 'array',
    ];

    protected $appends = [
        'category',
        'allowance_fixed',
        'tax_ptkp_status',
        'leave_balance',
        'shift_id',
        'office_location_id',
        'golongan_id',
    ];

    public function getCategoryAttribute(): string
    {
        return $this->custom_attributes['category'] ?? ($this->department_id === 2 ? 'TKL' : 'TKTL');
    }

    public function setCategoryAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['category'] = $value;
        $this->custom_attributes = $attrs;
    }

    public function getAllowanceFixedAttribute(): float
    {
        return (float) ($this->custom_attributes['allowance_fixed'] ?? 0);
    }

    public function setAllowanceFixedAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['allowance_fixed'] = (float) $value;
        $this->custom_attributes = $attrs;
    }

    public function getTaxPtkpStatusAttribute(): string
    {
        return $this->custom_attributes['tax_ptkp_status'] ?? 'TK/0';
    }

    public function setTaxPtkpStatusAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['tax_ptkp_status'] = $value;
        $this->custom_attributes = $attrs;
    }

    public function getLeaveBalanceAttribute(): int
    {
        return (int) ($this->custom_attributes['leave_balance'] ?? 12);
    }

    public function setLeaveBalanceAttribute($value): void
    {
        $attrs = $this->custom_attributes ?? [];
        $attrs['leave_balance'] = (int) $value;
        $this->custom_attributes = $attrs;
    }

    public function getShiftIdAttribute(): ?int
    {
        if (isset($this->attributes['shift_id'])) {
            return (int) $this->attributes['shift_id'];
        }

        return isset($this->custom_attributes['shift_id']) ? (int) $this->custom_attributes['shift_id'] : null;
    }

    public function setShiftIdAttribute($value): void
    {
        $val = $value ? (int) $value : null;
        $this->attributes['shift_id'] = $val;
        $attrs = $this->custom_attributes ?? [];
        $attrs['shift_id'] = $val;
        $this->custom_attributes = $attrs;
    }

    public function getOfficeLocationIdAttribute(): ?int
    {
        if (isset($this->attributes['office_location_id'])) {
            return (int) $this->attributes['office_location_id'];
        }

        return isset($this->custom_attributes['office_location_id']) ? (int) $this->custom_attributes['office_location_id'] : null;
    }

    public function setOfficeLocationIdAttribute($value): void
    {
        $val = $value ? (int) $value : null;
        $this->attributes['office_location_id'] = $val;
        $attrs = $this->custom_attributes ?? [];
        $attrs['office_location_id'] = $val;
        $this->custom_attributes = $attrs;
    }

    public function getGolonganIdAttribute(): ?int
    {
        return $this->attributes['grade_id'] ?? null;
    }

    public function setGolonganIdAttribute($value): void
    {
        $this->attributes['grade_id'] = $value;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class, 'plant_id');
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(HrisMasterGolongan::class, 'grade_id');
    }

    public function golongan(): BelongsTo
    {
        return $this->belongsTo(HrisMasterGolongan::class, 'grade_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(HrisShift::class, 'shift_id');
    }

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(HrisOfficeLocation::class, 'office_location_id');
    }

    public function timeBank(): HasOne
    {
        return $this->hasOne(HrisTimeBank::class, 'employee_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(HrisEmployeeSchedule::class, 'employee_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(HrisAttendance::class, 'employee_id');
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(HrisLeave::class, 'employee_id');
    }

    public function overtimes(): HasMany
    {
        return $this->hasMany(HrisOvertime::class, 'employee_id');
    }

    public function timeBanks(): HasMany
    {
        return $this->hasMany(HrisTimeBank::class, 'employee_id');
    }

    public function kizukis(): HasMany
    {
        return $this->hasMany(HrisKizuki::class, 'employee_id');
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(HrisSalary::class, 'employee_id');
    }

    public function kpis(): HasMany
    {
        return $this->hasMany(HrisKpi::class, 'employee_id');
    }
}
