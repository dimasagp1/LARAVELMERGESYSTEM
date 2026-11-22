<?php

namespace App\Modules\Hris\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrisShift extends Model
{
    use HasFactory;

    protected $table = 'hris_shifts';

    protected $guarded = ['id'];

    protected $casts = [
        'is_night_shift' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(HrisEmployeeSchedule::class, 'shift_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(HrisAttendance::class, 'shift_id');
    }
}
