<?php

namespace App\Modules\Hris\Services;

use App\Modules\Hris\Models\HrisAttendance;
use App\Modules\Hris\Models\HrisEmployee;
use App\Modules\Hris\Models\HrisOfficeLocation;
use App\Modules\Hris\Models\HrisShift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AttendanceCalculationService
{
    /**
     * Calculate geodesic distance in meters using Haversine formula.
     */
    public function calculateDistanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // in meters

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    /**
     * Process employee Smart Attendance Check-In with Geofence & Biometrics.
     */
    public function checkIn(
        int $employeeId,
        ?float $latitude = null,
        ?float $longitude = null,
        ?int $officeLocationId = null,
        ?int $shiftId = null,
        bool $faceVerified = true,
        ?string $photoPath = null,
        ?string $notes = null
    ): HrisAttendance {
        return DB::transaction(function () use (
            $employeeId, $latitude, $longitude, $officeLocationId, $shiftId, $faceVerified, $photoPath, $notes
        ) {
            $employee = HrisEmployee::findOrFail($employeeId);
            $today = Carbon::today()->toDateString();
            $nowTime = Carbon::now()->toTimeString();

            // Check Geofence if location specified
            if ($officeLocationId && $latitude !== null && $longitude !== null) {
                $location = HrisOfficeLocation::find($officeLocationId);
                if ($location) {
                    $distance = $this->calculateDistanceMeters(
                        (float) $location->latitude,
                        (float) $location->longitude,
                        $latitude,
                        $longitude
                    );

                    if ($distance > $location->radius_meters) {
                        throw new InvalidArgumentException("Lokasi berada di luar radius kantor ({$distance}m > {$location->radius_meters}m). Presensi ditolak.");
                    }
                }
            }

            // Determine Shift
            $shift = $shiftId ? HrisShift::find($shiftId) : HrisShift::where('is_active', true)->first();
            $shiftStartTime = $shift ? Carbon::parse($shift->start_time) : Carbon::parse('08:00:00');
            $tolerance = $shift ? $shift->late_tolerance_minutes : 15;
            $maxAllowedTime = $shiftStartTime->copy()->addMinutes($tolerance);

            $checkInCarbon = Carbon::parse($nowTime);
            $lateMinutes = 0;
            if ($checkInCarbon->greaterThan($maxAllowedTime)) {
                $lateMinutes = (int) $shiftStartTime->diffInMinutes($checkInCarbon);
            }

            $status = $lateMinutes > 0 ? 'late' : 'present';

            $attendance = HrisAttendance::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'date' => $today,
                ],
                [
                    'shift_id' => $shift?->id,
                    'office_location_id' => $officeLocationId,
                    'check_in_time' => $nowTime,
                    'check_in_latitude' => $latitude,
                    'check_in_longitude' => $longitude,
                    'check_in_face_verified' => $faceVerified,
                    'check_in_photo_path' => $photoPath,
                    'late_minutes' => $lateMinutes,
                    'status' => $status,
                    'notes' => $notes,
                ]
            );

            return $attendance->load(['employee.user', 'shift', 'officeLocation']);
        });
    }

    /**
     * Process employee Check-Out and calculate effective work hours.
     */
    public function checkOut(
        int $employeeId,
        ?string $notes = null
    ): HrisAttendance {
        return DB::transaction(function () use ($employeeId, $notes) {
            $today = Carbon::today()->toDateString();
            $nowTime = Carbon::now()->toTimeString();

            $attendance = HrisAttendance::where('employee_id', $employeeId)
                ->where('date', $today)
                ->firstOrFail();

            $checkIn = Carbon::parse($attendance->check_in_time ?? '08:00:00');
            $checkOut = Carbon::parse($nowTime);

            $diffMinutes = max(0, $checkIn->diffInMinutes($checkOut));
            // Minus 60 minutes lunch break if work time > 5 hours
            if ($diffMinutes > 300) {
                $diffMinutes = max(0, $diffMinutes - 60);
            }

            $workHours = round($diffMinutes / 60, 2);

            // Calculate early leave minutes against shift end time
            $earlyLeaveMinutes = 0;
            if ($attendance->shift) {
                $shiftEndTime = Carbon::parse($attendance->shift->end_time);
                if ($checkOut->lessThan($shiftEndTime)) {
                    $earlyLeaveMinutes = (int) $checkOut->diffInMinutes($shiftEndTime);
                }
            }

            $attendance->update([
                'check_out_time' => $nowTime,
                'work_hours' => $workHours,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'notes' => $notes ?? $attendance->notes,
            ]);

            return $attendance->fresh(['employee.user', 'shift', 'officeLocation']);
        });
    }

    /**
     * Helper for controller to process Check-In and return summary.
     */
    public function processCheckIn(
        int $employeeId,
        float $latitude,
        float $longitude,
        ?int $shiftId = null,
        ?int $officeLocationId = null,
        ?string $selfieImagePath = null,
        ?string $notes = null
    ): array {
        $att = $this->checkIn(
            employeeId: $employeeId,
            latitude: $latitude,
            longitude: $longitude,
            officeLocationId: $officeLocationId,
            shiftId: $shiftId,
            photoPath: $selfieImagePath,
            notes: $notes
        );

        return [
            'attendance' => $att,
            'is_late' => $att->is_late,
            'late_minutes' => $att->late_minutes,
            'distance_meters' => $att->distance_meters,
        ];
    }

    /**
     * Helper for controller to process Check-Out and return summary.
     */
    public function processCheckOut(
        int $attendanceId,
        float $latitude,
        float $longitude
    ): array {
        $attendance = HrisAttendance::findOrFail($attendanceId);
        $att = $this->checkOut($attendance->employee_id);

        return [
            'attendance' => $att,
            'work_hours' => $att->work_hours,
        ];
    }
}
