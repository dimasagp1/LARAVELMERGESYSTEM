<?php

namespace App\Modules\Hris\Controllers;

use App\Core\Models\Department;
use App\Http\Controllers\Controller;
use App\Modules\Hris\Models\HrisAttendance;
use App\Modules\Hris\Models\HrisEmployee;
use App\Modules\Hris\Models\HrisOfficeLocation;
use App\Modules\Hris\Models\HrisShift;
use App\Modules\Hris\Services\AttendanceCalculationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceCalculationService $attendanceService
    ) {}

    /**
     * Display daily attendance log with geofencing status.
     */
    public function index(Request $request): Response
    {
        $selectedDate = $request->input('date', Carbon::today()->toDateString());

        $query = HrisAttendance::with([
            'employee.user',
            'employee.department',
            'shift',
            'officeLocation',
        ])->where('date', $selectedDate);

        if ($deptId = $request->input('department_id')) {
            $query->whereHas('employee', fn ($q) => $q->where('department_id', $deptId));
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $attendances = $query->latest('check_in_time')->paginate(20)->withQueryString();

        // Summary metrics for the day
        $allToday = HrisAttendance::where('date', $selectedDate)->get();
        $totalPresent = $allToday->whereIn('status', ['present', 'late'])->count();
        $totalLate = $allToday->where('status', 'late')->count();
        $totalLeave = $allToday->where('status', 'leave')->count();

        return Inertia::render('Modules/Hris/Attendance/Index', [
            'attendances' => $attendances,
            'selectedDate' => $selectedDate,
            'filters' => $request->only(['date', 'department_id', 'status']),
            'summary' => [
                'total_present' => $totalPresent,
                'total_late' => $totalLate,
                'total_leave' => $totalLeave,
            ],
            'departments' => Department::select('id', 'name', 'code')->get(),
            'officeLocations' => HrisOfficeLocation::where('is_active', true)->get(),
            'shifts' => HrisShift::where('is_active', true)->get(),
            'employees' => HrisEmployee::with('user')->where('status', 'active')->select('id', 'user_id', 'nik', 'position', 'shift_id', 'office_location_id')->get(),
        ]);
    }

    /**
     * Process biometric/mobile geofenced Check-In.
     */
    public function checkIn(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:hris_employees,id',
            'shift_id' => 'nullable|exists:hris_shifts,id',
            'office_location_id' => 'nullable|exists:hris_office_locations,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'selfie_image_path' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $result = $this->attendanceService->processCheckIn(
                employeeId: (int) $validated['employee_id'],
                latitude: (float) $validated['latitude'],
                longitude: (float) $validated['longitude'],
                shiftId: ! empty($validated['shift_id']) ? (int) $validated['shift_id'] : null,
                officeLocationId: ! empty($validated['office_location_id']) ? (int) $validated['office_location_id'] : null,
                selfieImagePath: $validated['selfie_image_path'] ?? null,
                notes: $validated['notes'] ?? null
            );

            $msg = 'Presensi Masuk Berhasil dicatat (Jarak: '.$result['distance_meters'].'m).';
            if ($result['is_late']) {
                $msg .= ' Terlambat '.$result['late_minutes'].' menit.';
            }

            return redirect()->back()->with('success', $msg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses presensi: '.$e->getMessage());
        }
    }

    /**
     * Process Check-Out.
     */
    public function checkOut(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'attendance_id' => 'required|exists:hris_attendances,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        try {
            $result = $this->attendanceService->processCheckOut(
                attendanceId: (int) $validated['attendance_id'],
                latitude: (float) $validated['latitude'],
                longitude: (float) $validated['longitude']
            );

            return redirect()->back()->with('success', 'Presensi Pulang Berhasil. Total jam kerja: '.$result['work_hours'].' jam.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses check-out: '.$e->getMessage());
        }
    }
}
