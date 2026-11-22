<?php

namespace App\Modules\Hris\Models;

use App\Core\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrisDepartmentObjective extends Model
{
    use HasFactory;

    protected $table = 'hris_department_objectives';

    protected $guarded = ['id'];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    protected $appends = [
        'kpi_name',
        'perspective',
        'target_value',
        'target_unit',
        'achievement_percentage',
        'is_locked',
    ];

    public function getKpiNameAttribute(): string
    {
        return $this->attributes['name'] ?? '';
    }

    public function setKpiNameAttribute($value): void
    {
        $this->attributes['name'] = $value;
    }

    public function getPerspectiveAttribute(): string
    {
        return $this->attributes['target_formula'] ?? 'internal_process';
    }

    public function setPerspectiveAttribute($value): void
    {
        $this->attributes['target_formula'] = $value;
    }

    public function getTargetValueAttribute(): float
    {
        $latest = $this->achievements()->latest('id')->first();

        return (float) ($latest?->target_value ?? 100);
    }

    public function getTargetUnitAttribute(): string
    {
        return '%';
    }

    public function getAchievementPercentageAttribute(): float
    {
        $latest = $this->achievements()->latest('id')->first();

        return (float) ($latest?->achievement_percentage ?? 95);
    }

    public function getIsLockedAttribute(): bool
    {
        $latest = $this->achievements()->latest('id')->first();

        return $latest?->status === 'locked' || $latest?->status === 'pushed_to_bsc';
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(HrisDepartmentObjectiveAchievement::class, 'department_objective_id');
    }

    public function workPrograms(): HasMany
    {
        return $this->hasMany(HrisDepartmentWorkProgram::class, 'objective_id');
    }
}
