<?php

namespace App\Modules\Hris\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrisEmployeeSchedule extends Model
{
    use HasFactory;

    protected $table = 'hris_employee_schedules';

    protected $guarded = ['id'];

    protected $casts = [
        'schedule_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrisEmployee::class, 'employee_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(HrisShift::class, 'shift_id');
    }
}
