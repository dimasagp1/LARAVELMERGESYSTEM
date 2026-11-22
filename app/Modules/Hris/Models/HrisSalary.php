<?php

namespace App\Modules\Hris\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class HrisSalary extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'hris_salaries';

    protected $guarded = ['id'];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'fixed_allowances' => 'decimal:2',
        'attendance_allowances' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'late_deductions' => 'decimal:2',
        'unpaid_leave_deductions' => 'decimal:2',
        'bpjs_tk_deduction' => 'decimal:2',
        'bpjs_kes_deduction' => 'decimal:2',
        'pph21_ter_deduction' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
        'custom_attributes' => 'array',
    ];

    protected $appends = [
        'allowance_fixed',
        'allowance_attendance',
        'bpjs_tk_employee',
        'bpjs_kes_employee',
        'pph21_amount',
        'deduction_late',
        'total_deductions',
        'gross_salary',
        'status',
    ];

    public function getAllowanceFixedAttribute(): float
    {
        return (float) ($this->fixed_allowances ?? 0);
    }

    public function setAllowanceFixedAttribute($value): void
    {
        $this->attributes['fixed_allowances'] = $value;
    }

    public function getAllowanceAttendanceAttribute(): float
    {
        return (float) ($this->attendance_allowances ?? 0);
    }

    public function setAllowanceAttendanceAttribute($value): void
    {
        $this->attributes['attendance_allowances'] = $value;
    }

    public function getBpjsTkEmployeeAttribute(): float
    {
        return (float) ($this->bpjs_tk_deduction ?? 0);
    }

    public function setBpjsTkEmployeeAttribute($value): void
    {
        $this->attributes['bpjs_tk_deduction'] = $value;
    }

    public function getBpjsKesEmployeeAttribute(): float
    {
        return (float) ($this->bpjs_kes_deduction ?? 0);
    }

    public function setBpjsKesEmployeeAttribute($value): void
    {
        $this->attributes['bpjs_kes_deduction'] = $value;
    }

    public function getPph21AmountAttribute(): float
    {
        return (float) ($this->pph21_ter_deduction ?? 0);
    }

    public function setPph21AmountAttribute($value): void
    {
        $this->attributes['pph21_ter_deduction'] = $value;
    }

    public function getDeductionLateAttribute(): float
    {
        return (float) ($this->late_deductions ?? 0);
    }

    public function setDeductionLateAttribute($value): void
    {
        $this->attributes['late_deductions'] = $value;
    }

    public function getTotalDeductionsAttribute(): float
    {
        return (float) (($this->bpjs_tk_deduction ?? 0) + ($this->bpjs_kes_deduction ?? 0) + ($this->pph21_ter_deduction ?? 0) + ($this->late_deductions ?? 0) + ($this->unpaid_leave_deductions ?? 0));
    }

    public function getGrossSalaryAttribute(): float
    {
        return (float) (($this->basic_salary ?? 0) + ($this->fixed_allowances ?? 0) + ($this->attendance_allowances ?? 0) + ($this->overtime_pay ?? 0));
    }

    public function getStatusAttribute(): string
    {
        return $this->payment_status ?? 'approved';
    }

    public function setStatusAttribute($value): void
    {
        $this->attributes['payment_status'] = $value;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrisEmployee::class, 'employee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
