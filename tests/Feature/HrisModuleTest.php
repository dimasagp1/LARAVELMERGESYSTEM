<?php

namespace Tests\Feature;

use App\Core\Models\Department;
use App\Models\User;
use App\Modules\Hris\Models\HrisAttendance;
use App\Modules\Hris\Models\HrisDepartmentObjective;
use App\Modules\Hris\Models\HrisEmployee;
use App\Modules\Hris\Models\HrisKizuki;
use App\Modules\Hris\Models\HrisLeave;
use App\Modules\Hris\Models\HrisLeaveCategory;
use App\Modules\Hris\Models\HrisMasterGolongan;
use App\Modules\Hris\Models\HrisOfficeLocation;
use App\Modules\Hris\Models\HrisOvertime;
use App\Modules\Hris\Models\HrisSalary;
use App\Modules\Hris\Models\HrisShift;
use Database\Seeders\CoreMasterDataSeeder;
use Database\Seeders\DefaultErpModulesSeeder;
use Database\Seeders\HrisModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HrisModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected HrisEmployee $employee;

    protected Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DefaultErpModulesSeeder::class);
        $this->seed(CoreMasterDataSeeder::class);
        $this->seed(HrisModuleSeeder::class);

        $this->user = User::where('email', 'admin@herbatech.com')->first();
        $this->employee = HrisEmployee::first();
        $this->department = Department::first();
    }

    public function test_hris_overview_dashboard_renders(): void
    {
        $response = $this->actingAs($this->user)->get('/hris');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Modules/Hris/Overview')
            ->has('metrics.kpis')
            ->has('metrics.charts')
        );
    }

    public function test_employee_index_and_create_workflow(): void
    {
        $response = $this->actingAs($this->user)->get('/hris/employees');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Modules/Hris/Employee/Index'));

        $createResponse = $this->actingAs($this->user)->get('/hris/employees/create');
        $createResponse->assertStatus(200);

        $golongan = HrisMasterGolongan::first();

        $postData = [
            'name' => 'Ahmad Dahlan',
            'email' => 'ahmad.dahlan@herbatech.com',
            'department_id' => $this->department->id,
            'golongan_id' => $golongan->id,
            'position' => 'Staff Formulasi R&D',
            'employment_status' => 'permanent',
            'join_date' => '2026-01-10',
            'basic_salary' => 6500000,
            'allowance_fixed' => 500000,
            'tax_ptkp_status' => 'TK/0',
            'bank_name' => 'BCA',
            'bank_account_no' => '1234567890',
            'leave_balance' => 12,
        ];

        $storeResponse = $this->actingAs($this->user)->post('/hris/employees', $postData);
        $storeResponse->assertRedirect('/hris/employees');

        $this->assertDatabaseHas('hris_employees', [
            'position' => 'Staff Formulasi R&D',
            'basic_salary' => 6500000,
        ]);

        $createdEmp = HrisEmployee::where('position', 'Staff Formulasi R&D')->first();
        $showResponse = $this->actingAs($this->user)->get("/hris/employees/{$createdEmp->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertInertia(fn ($page) => $page->component('Modules/Hris/Employee/Show'));
    }

    public function test_attendance_check_in_and_check_out(): void
    {
        $response = $this->actingAs($this->user)->get('/hris/attendance');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Modules/Hris/Attendance/Index'));

        $shift = HrisShift::first();
        $location = HrisOfficeLocation::first();

        // Create new employee specifically for this test
        $newEmp = HrisEmployee::create([
            'user_id' => $this->user->id,
            'nik' => 'TEST-ATT-001',
            'department_id' => $this->department->id,
            'position' => 'Operator Test',
            'join_date' => '2026-01-01',
            'basic_salary' => 4000000,
            'status' => 'active',
        ]);

        // Check-in
        $checkInResponse = $this->actingAs($this->user)->post('/hris/attendance/check-in', [
            'employee_id' => $newEmp->id,
            'shift_id' => $shift->id,
            'office_location_id' => $location->id,
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'notes' => 'Test presensi masuk',
        ]);

        $checkInResponse->assertSessionHas('success');

        $this->assertDatabaseHas('hris_attendances', [
            'employee_id' => $newEmp->id,
            'date' => now()->toDateString(),
        ]);

        $att = HrisAttendance::where('employee_id', $newEmp->id)->first();

        // Check-out
        $checkOutResponse = $this->actingAs($this->user)->post('/hris/attendance/check-out', [
            'attendance_id' => $att->id,
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
        ]);

        $checkOutResponse->assertSessionHas('success');

        $att->refresh();
        $this->assertNotNull($att->check_out_time);
    }

    public function test_leave_request_and_approval_workflow(): void
    {
        $category = HrisLeaveCategory::first();

        $createResponse = $this->actingAs($this->user)->post('/hris/leaves', [
            'employee_id' => $this->employee->id,
            'leave_category_id' => $category->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(6)->toDateString(),
            'reason' => 'Keperluan keluarga mendesak',
            'emergency_phone' => '08123456789',
        ]);

        $createResponse->assertRedirect('/hris/leaves');

        $leave = HrisLeave::where('employee_id', $this->employee->id)
            ->where('reason', 'Keperluan keluarga mendesak')
            ->first();

        $this->assertNotNull($leave);
        $this->assertEquals('pending_spv', $leave->status);

        // Approve SPV
        $this->actingAs($this->user)->post("/hris/leaves/{$leave->id}/approve", [
            'action' => 'approve_spv',
        ]);

        $leave->refresh();
        $this->assertEquals('pending_hr', $leave->status);

        // Approve HR
        $this->actingAs($this->user)->post("/hris/leaves/{$leave->id}/approve", [
            'action' => 'approve_hr',
        ]);

        $leave->refresh();
        $this->assertEquals('approved', $leave->status);

        // Test PDF Export
        $pdfResponse = $this->actingAs($this->user)->get("/hris/leaves/{$leave->id}/pdf");
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_overtime_spl_calculation_and_approval(): void
    {
        $postData = [
            'employee_id' => $this->employee->id,
            'date' => now()->toDateString(),
            'start_time' => '17:00',
            'end_time' => '21:00',
            'is_holiday' => false,
            'reason' => 'Target rilis batch kapsul herbal',
            'work_target' => 'Ekstraksi simplisia 100kg',
            'compensation_type' => 'cash',
        ];

        $response = $this->actingAs($this->user)->post('/hris/overtime', $postData);
        $response->assertRedirect('/hris/overtime');

        $spl = HrisOvertime::where('employee_id', $this->employee->id)
            ->where('reason', 'Target rilis batch kapsul herbal')
            ->first();

        $this->assertNotNull($spl);
        $this->assertEquals(4.0, (float) $spl->total_hours);
        $this->assertEquals(7.5, (float) $spl->multiplier_hours); // Depnaker: 1h x 1.5 + 3h x 2.0 = 7.5

        // Approve OM
        $this->actingAs($this->user)->post("/hris/overtime/{$spl->id}/approve", [
            'action' => 'approve_om',
        ]);

        $spl->refresh();
        $this->assertEquals('approved', $spl->status);

        // Test PDF Export
        $pdfResponse = $this->actingAs($this->user)->get("/hris/overtime/{$spl->id}/pdf");
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_kizuki_kaizen_submission_and_scoring(): void
    {
        $postData = [
            'employee_id' => $this->employee->id,
            'department_id' => $this->department->id,
            'title' => 'Inovasi Penataan Tooling Mesin Tablet',
            'category' => '5s',
            'current_condition' => 'Pencarian punch dies memakan waktu 15 menit',
            'proposed_idea' => 'Membuat rak gantung dengan nomor identifikasi punch',
            'expected_benefit' => 'Menghemat waktu pergantian mold 10 menit',
        ];

        $response = $this->actingAs($this->user)->post('/hris/kizuki', $postData);
        $response->assertRedirect('/hris/kizuki');

        $kizuki = HrisKizuki::where('title', 'Inovasi Penataan Tooling Mesin Tablet')->first();
        $this->assertNotNull($kizuki);
        $this->assertEquals('submitted', $kizuki->status);

        // Review with QCDSM Scoring
        $reviewData = [
            'status' => 'implemented',
            'quality_score' => 90,
            'cost_saving_score' => 85,
            'delivery_score' => 80,
            'safety_score' => 95,
            'morale_score' => 90,
            'reward_points' => 70,
            'review_notes' => 'Ide perbaikan sangat baik dan langsung diterapkan di lini tablet.',
        ];

        $reviewResponse = $this->actingAs($this->user)->post("/hris/kizuki/{$kizuki->id}/review", $reviewData);
        $reviewResponse->assertSessionHas('success');

        $kizuki->refresh();
        $this->assertEquals('implemented', $kizuki->status);
        $this->assertEquals(70, $kizuki->reward_points);
    }

    public function test_payroll_calculation_and_payslip_export(): void
    {
        $month = now()->month;
        $year = now()->year;

        $calcResponse = $this->actingAs($this->user)->post('/hris/payroll/calculate', [
            'period_month' => $month,
            'period_year' => $year,
        ]);

        $calcResponse->assertRedirect();
        $calcResponse->assertSessionHas('success');

        $salary = HrisSalary::where('employee_id', $this->employee->id)
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->first();

        $this->assertNotNull($salary);
        $this->assertGreaterThan(0, (float) $salary->net_salary);

        // Show payslip
        $showResponse = $this->actingAs($this->user)->get("/hris/payroll/{$salary->id}");
        $showResponse->assertStatus(200);
        $showResponse->assertInertia(fn ($page) => $page->component('Modules/Hris/Payroll/Show'));

        // PDF export
        $pdfResponse = $this->actingAs($this->user)->get("/hris/payroll/{$salary->id}/pdf");
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('content-type'));
    }

    public function test_strategic_bsc_objectives_and_work_programs(): void
    {
        $response = $this->actingAs($this->user)->get('/hris/strategic');
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Modules/Hris/Strategic/Index'));

        // Create L3 Objective
        $objResponse = $this->actingAs($this->user)->post('/hris/strategic/objectives', [
            'department_id' => $this->department->id,
            'period_year' => now()->year,
            'perspective' => 'internal_process',
            'kpi_name' => 'OEE Mesin Blister Herbal Di Atas 85%',
            'target_value' => 85,
            'target_unit' => '%',
            'polarity' => 'maximize',
            'weight' => 25,
        ]);

        $objResponse->assertSessionHas('success');

        $objective = HrisDepartmentObjective::where('name', 'OEE Mesin Blister Herbal Di Atas 85%')->first();
        $this->assertNotNull($objective);

        // Create L4 Program
        $progResponse = $this->actingAs($this->user)->post('/hris/strategic/programs', [
            'department_objective_id' => $objective->id,
            'program_name' => 'Preventive Maintenance Mingguan Lini Blister',
            'pic_name' => 'Teknisi Maintenance',
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'budget' => 2500000,
            'progress_pct' => 50,
        ]);

        $progResponse->assertSessionHas('success');

        // Push to BSC
        $pushResponse = $this->actingAs($this->user)->post("/hris/strategic/objectives/{$objective->id}/push-to-bsc");
        $pushResponse->assertSessionHas('success');
    }
}
