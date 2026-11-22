<?php

namespace App\Modules\Hris\Models;

use App\Core\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrisDepartmentWorkProgram extends Model
{
    use HasFactory;

    protected $table = 'hris_department_work_programs';

    protected $guarded = ['id'];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
    ];

    protected $appends = [
        'progress_pct',
        'pic_name',
        'start_date',
        'end_date',
    ];

    public function getProgressPctAttribute(): float
    {
        return (float) ($this->progress_percentage ?? 0);
    }

    public function setProgressPctAttribute($value): void
    {
        $this->attributes['progress_percentage'] = $value;
    }

    public function getPicNameAttribute(): string
    {
        return 'Penanggung Jawab Lini';
    }

    public function getStartDateAttribute(): string
    {
        return date('Y-01-01');
    }

    public function getEndDateAttribute(): string
    {
        return date('Y-12-31');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function objective(): BelongsTo
    {
        return $this->belongsTo(HrisDepartmentObjective::class, 'objective_id');
    }

    public function departmentObjective(): BelongsTo
    {
        return $this->belongsTo(HrisDepartmentObjective::class, 'objective_id');
    }
}
