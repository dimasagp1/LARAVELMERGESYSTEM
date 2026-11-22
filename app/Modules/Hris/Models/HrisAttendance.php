<?php

namespace App\Modules\Hris\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrisAttendance extends Model
{
    use HasFactory;

    protected $table = 'hris_attendances';

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'check_in_face_verified' => 'boolean',
        'late_minutes' => 'integer',
        'early_leave_minutes' => 'integer',
        'work_hours' => 'decimal:2',
        'custom_attributes' => 'array',
    ];

    protected $appends = [
        'distance_meters',
        'is_late',
        'is_geofence_valid',
    ];

    public function getDistanceMetersAttribute(): ?int
    {
        return isset($this->custom_attributes['distance_meters']) ? (int) $this->custom_attributes['distance_meters'] : 25;
    }

    public function getIsLateAttribute(): bool
    {
        return $this->late_minutes > 0 || $this->status === 'late';
    }

    public function getIsGeofenceValidAttribute(): bool
    {
        return (bool) ($this->custom_attributes['is_geofence_valid'] ?? true);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(HrisEmployee::class, 'employee_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(HrisEmployeeSchedule::class, 'schedule_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(HrisShift::class, 'shift_id');
    }

    public function officeLocation(): BelongsTo
    {
        return $this->belongsTo(HrisOfficeLocation::class, 'office_location_id');
    }
}
