<?php

namespace App\Modules\Hris\Controllers;

use App\Core\Models\Department;
use App\Core\Models\Plant;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Hris\Models\HrisEmployee;
use App\Modules\Hris\Models\HrisMasterGolongan;
use App\Modules\Hris\Models\HrisOfficeLocation;
use App\Modules\Hris\Models\HrisShift;
use App\Modules\Hris\Services\HrisNumberingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function __construct(
        protected HrisNumberingService $numberingService
    ) {}

    /**
     * Display a listing of employees with filters.
     */
    public function index(Request $request): Response
    {
        $query = HrisEmployee::with(['user', 'department', 'plant', 'golongan', 'shift', 'officeLocation']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($deptId = $request->input('department_id')) {
            $query->where('department_id', $deptId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($empStatus = $request->input('employment_status')) {
            $query->where('employment_status', $empStatus);
        }

        $employees = $query->latest('id')->paginate(12)->withQueryString();

        return Inertia::render('Modules/Hris/Employee/Index', [
            'employees' => $employees,
            'filters' => $request->only(['search', 'department_id', 'status', 'employment_status']),
            'departments' => Department::select('id', 'name', 'code')->get(),
            'plants' => Plant::select('id', 'name', 'code')->get(),
            'golongans' => HrisMasterGolongan::select('id', 'code', 'name', 'level')->orderBy('level')->get(),
        ]);
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create(): Response
    {
        return Inertia::render('Modules/Hris/Employee/Form', [
            'employee' => null,
            'departments' => Department::select('id', 'name', 'code')->get(),
            'plants' => Plant::select('id', 'name', 'code')->get(),
            'golongans' => HrisMasterGolongan::select('id', 'code', 'name', 'level', 'min_salary', 'max_salary')->orderBy('level')->get(),
            'shifts' => HrisShift::where('is_active', true)->get(),
            'officeLocations' => HrisOfficeLocation::where('is_active', true)->get(),
        ]);
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'department_id' => 'required|exists:departments,id',
            'plant_id' => 'nullable|exists:plants,id',
            'golongan_id' => 'required|exists:hris_master_golongans,id',
            'shift_id' => 'nullable|exists:hris_shifts,id',
            'office_location_id' => 'nullable|exists:hris_office_locations,id',
            'position' => 'required|string|max:255',
            'employment_status' => 'required|in:permanent,contract,probation,internship,pkwtt,pkwt,intern',
            'join_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:join_date',
            'basic_salary' => 'required|numeric|min:0',
            'allowance_fixed' => 'nullable|numeric|min:0',
            'tax_ptkp_status' => 'required|string|max:10',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:50',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'leave_balance' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $department = Department::findOrFail($validated['department_id']);
            $nik = $this->numberingService->generateNik($department->code);

            $statusMap = [
                'permanent' => 'pkwtt',
                'pkwtt' => 'pkwtt',
                'contract' => 'pkwt',
                'pkwt' => 'pkwt',
                'probation' => 'probation',
                'internship' => 'intern',
                'intern' => 'intern',
            ];
            $dbEmploymentStatus = $statusMap[$validated['employment_status']] ?? 'pkwtt';

            // Create user account for login & core integration
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make('password123'),
                'department_id' => $validated['department_id'],
                'status' => 'active',
            ]);

            $customAttributes = [
                'allowance_fixed' => $validated['allowance_fixed'] ?? 0,
                'tax_ptkp_status' => $validated['tax_ptkp_status'] ?? 'TK/0',
                'leave_balance' => $validated['leave_balance'] ?? 12,
                'shift_id' => $validated['shift_id'] ?? null,
                'office_location_id' => $validated['office_location_id'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            ];

            HrisEmployee::create([
                'user_id' => $user->id,
                'nik' => $nik,
                'department_id' => $validated['department_id'],
                'plant_id' => $validated['plant_id'] ?? null,
                'grade_id' => $validated['golongan_id'],
                'position' => $validated['position'],
                'employment_status' => $dbEmploymentStatus,
                'join_date' => $validated['join_date'],
                'status' => 'active',
                'basic_salary' => $validated['basic_salary'],
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_no' => $validated['bank_account_no'] ?? null,
                'custom_attributes' => $customAttributes,
            ]);
        });

        return redirect()->route('hris.employees.index')
            ->with('success', 'Data karyawan baru berhasil didaftarkan.');
    }

    /**
     * Display the specified employee profile.
     */
    public function show(int $id): Response
    {
        $employee = HrisEmployee::with([
            'user',
            'department',
            'plant',
            'golongan',
            'shift',
            'officeLocation',
            'timeBank',
            'attendances' => fn ($q) => $q->latest('date')->take(10),
            'leaves' => fn ($q) => $q->latest('created_at')->take(5),
            'overtimes' => fn ($q) => $q->latest('date')->take(5),
            'salaries' => fn ($q) => $q->latest('id')->take(6),
        ])->findOrFail($id);

        return Inertia::render('Modules/Hris/Employee/Show', [
            'employee' => $employee,
        ]);
    }

    /**
     * Show the form for editing an employee.
     */
    public function edit(int $id): Response
    {
        $employee = HrisEmployee::with(['user'])->findOrFail($id);

        return Inertia::render('Modules/Hris/Employee/Form', [
            'employee' => $employee,
            'departments' => Department::select('id', 'name', 'code')->get(),
            'plants' => Plant::select('id', 'name', 'code')->get(),
            'golongans' => HrisMasterGolongan::select('id', 'code', 'name', 'level', 'min_salary', 'max_salary')->orderBy('level')->get(),
            'shifts' => HrisShift::where('is_active', true)->get(),
            'officeLocations' => HrisOfficeLocation::where('is_active', true)->get(),
        ]);
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $employee = HrisEmployee::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'plant_id' => 'nullable|exists:plants,id',
            'golongan_id' => 'required|exists:hris_master_golongans,id',
            'shift_id' => 'nullable|exists:hris_shifts,id',
            'office_location_id' => 'nullable|exists:hris_office_locations,id',
            'position' => 'required|string|max:255',
            'employment_status' => 'required|in:permanent,contract,probation,internship,pkwtt,pkwt,intern',
            'status' => 'required|in:active,inactive,resigned,terminated',
            'join_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:join_date',
            'basic_salary' => 'required|numeric|min:0',
            'allowance_fixed' => 'nullable|numeric|min:0',
            'tax_ptkp_status' => 'required|string|max:10',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_no' => 'nullable|string|max:50',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:50',
            'leave_balance' => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($employee, $validated) {
            if ($employee->user) {
                $employee->user->update([
                    'name' => $validated['name'],
                    'department_id' => $validated['department_id'],
                    'status' => $validated['status'] === 'active' ? 'active' : 'inactive',
                ]);
            }

            $customAttributes = array_merge($employee->custom_attributes ?? [], [
                'allowance_fixed' => $validated['allowance_fixed'] ?? $employee->allowance_fixed,
                'tax_ptkp_status' => $validated['tax_ptkp_status'] ?? $employee->tax_ptkp_status,
                'leave_balance' => $validated['leave_balance'] ?? $employee->leave_balance,
                'shift_id' => $validated['shift_id'] ?? $employee->shift_id,
                'office_location_id' => $validated['office_location_id'] ?? $employee->office_location_id,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? ($employee->custom_attributes['emergency_contact_name'] ?? null),
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? ($employee->custom_attributes['emergency_contact_phone'] ?? null),
            ]);

            $statusMap = [
                'permanent' => 'pkwtt',
                'pkwtt' => 'pkwtt',
                'contract' => 'pkwt',
                'pkwt' => 'pkwt',
                'probation' => 'probation',
                'internship' => 'intern',
                'intern' => 'intern',
            ];
            $dbEmploymentStatus = $statusMap[$validated['employment_status']] ?? 'pkwtt';

            $employee->update([
                'department_id' => $validated['department_id'],
                'plant_id' => $validated['plant_id'] ?? null,
                'grade_id' => $validated['golongan_id'],
                'position' => $validated['position'],
                'employment_status' => $dbEmploymentStatus,
                'status' => $validated['status'],
                'join_date' => $validated['join_date'],
                'basic_salary' => $validated['basic_salary'],
                'bank_name' => $validated['bank_name'] ?? null,
                'bank_account_no' => $validated['bank_account_no'] ?? null,
                'custom_attributes' => $customAttributes,
            ]);
        });

        return redirect()->route('hris.employees.show', $id)
            ->with('success', 'Data profil karyawan berhasil diperbarui.');
    }
}
