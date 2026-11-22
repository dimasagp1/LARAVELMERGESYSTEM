<?php

namespace App\Modules\Hris\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrisDepartmentObjectiveAchievement extends Model
{
    use HasFactory;

    protected $table = 'hris_department_objective_achievements';

    protected $guarded = ['id'];

    protected $casts = [
        'target_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'achievement_percentage' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function objective(): BelongsTo
    {
        return $this->belongsTo(HrisDepartmentObjective::class, 'department_objective_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
