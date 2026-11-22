<?php

namespace Database\Seeders;

use App\Core\Models\Department;
use App\Core\Models\Plant;
use App\Models\User;
use App\Modules\Hris\Models\HrisAttendance;
use App\Modules\Hris\Models\HrisDepartmentObjective;
use App\Modules\Hris\Models\HrisDepartmentObjectiveAchievement;
use App\Modules\Hris\Models\HrisDepartmentWorkProgram;
use App\Modules\Hris\Models\HrisEmployee;
use App\Modules\Hris\Models\HrisKizuki;
use App\Modules\Hris\Models\HrisLeave;
use App\Modules\Hris\Models\HrisLeaveCategory;
use App\Modules\Hris\Models\HrisMasterGolongan;
use App\Modules\Hris\Models\HrisOfficeLocation;
use App\Modules\Hris\Models\HrisOvertime;
use App\Modules\Hris\Models\HrisSalary;
use App\Modules\Hris\Models\HrisShift;
use App\Modules\Hris\Models\HrisTimeBank;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HrisModuleSeeder extends Seeder
{
    /**
     * Run the HRIS database seeds.
     */
    public function run(): void
    {
        // 1. Master Golongan (G1 - G5)
        $golongans = [
            ['code' => 'G-1', 'grade_name' => 'Operator Pelaksana', 'level' => 1, 'min_salary' => 3800000, 'max_salary' => 4800000],
            ['code' => 'G-2', 'grade_name' => 'Senior Operator / Teknisi', 'level' => 2, 'min_salary' => 4800000, 'max_salary' => 6200000],
            ['code' => 'G-3', 'grade_name' => 'Staff / Formulator / Analis', 'level' => 3, 'min_salary' => 6200000, 'max_salary' => 9500000],
            ['code' => 'G-4', 'grade_name' => 'Supervisor / Section Head', 'level' => 4, 'min_salary' => 9500000, 'max_salary' => 15000000],
            ['code' => 'G-5', 'grade_name' => 'Assistant Manager / Manager', 'level' => 5, 'min_salary' => 15000000, 'max_salary' => 25000000],
        ];

        $golModels = [];
        foreach ($golongans as $g) {
            $golModels[$g['code']] = HrisMasterGolongan::updateOrCreate(['code' => $g['code']], $g);
        }

        // 2. Shifts
        $shifts = [
            ['code' => 'SFT-1', 'name' => 'Shift Pagi (Lini Produksi)', 'start_time' => '07:00:00', 'end_time' => '15:00:00', 'is_active' => true],
            ['code' => 'SFT-2', 'name' => 'Shift Siang (Lini Produksi)', 'start_time' => '15:00:00', 'end_time' => '23:00:00', 'is_active' => true],
            ['code' => 'SFT-3', 'name' => 'Shift Malam (Sanitasi Mesin)', 'start_time' => '23:00:00', 'end_time' => '07:00:00', 'is_active' => true],
            ['code' => 'NON-SFT', 'name' => 'Reguler Kantor Non-Shift', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'is_active' => true],
        ];

        $shiftModels = [];
        foreach ($shifts as $s) {
            $shiftModels[$s['code']] = HrisShift::updateOrCreate(['code' => $s['code']], $s);
        }

        // 3. Office Locations (Sukoharjo, Boyolali, Solo)
        $locations = [
            ['name' => 'Pabrik Herbatech Sukoharjo Plant', 'latitude' => -7.681240, 'longitude' => 110.835420, 'radius_meters' => 100, 'address' => 'Kawasan Industri Sukoharjo', 'is_active' => true],
            ['name' => 'Pabrik Ekstraksi Boyolali Plant', 'latitude' => -7.536000, 'longitude' => 110.596000, 'radius_meters' => 120, 'address' => 'Jl. Pandanaran Boyolali', 'is_active' => true],
            ['name' => 'Head Office Solo Paragon', 'latitude' => -7.566600, 'longitude' => 110.828000, 'radius_meters' => 80, 'address' => 'Solo Paragon Tower', 'is_active' => true],
        ];

        $locModels = [];
        foreach ($locations as $l) {
            $locModels[$l['name']] = HrisOfficeLocation::updateOrCreate(['name' => $l['name']], $l);
        }

        // 4. Leave Categories
        $categories = [
            ['code' => 'LV-ANNUAL', 'name' => 'Cuti Tahunan', 'default_quota' => 12, 'is_paid' => true, 'requires_attachment' => false],
            ['code' => 'LV-SICK', 'name' => 'Cuti Sakit (Surat Dokter)', 'default_quota' => 14, 'is_paid' => true, 'requires_attachment' => true],
            ['code' => 'LV-MATERNITY', 'name' => 'Cuti Melahirkan (Maternity)', 'default_quota' => 90, 'is_paid' => true, 'requires_attachment' => true],
            ['code' => 'LV-MARRIAGE', 'name' => 'Cuti Menikah Karyawan', 'default_quota' => 3, 'is_paid' => true, 'requires_attachment' => false],
            ['code' => 'LV-BEREAVEMENT', 'name' => 'Izin Duka Cita Keluarga', 'default_quota' => 2, 'is_paid' => true, 'requires_attachment' => false],
        ];

        $catModels = [];
        foreach ($categories as $c) {
            $catModels[$c['code']] = HrisLeaveCategory::updateOrCreate(['code' => $c['code']], $c);
        }

        // 5. Employees & Linked Users
        $deptProd = Department::where('code', 'PROD')->first() ?? Department::first();
        $deptRnd = Department::where('code', 'RND')->first() ?? Department::first();
        $deptQc = Department::where('code', 'QC')->first() ?? Department::first();
        $deptScm = Department::where('code', 'SCM')->first() ?? Department::first();
        $deptFat = Department::where('code', 'FAT')->first() ?? Department::first();
        $deptHr = Department::where('code', 'HR')->first() ?? Department::first();

        $plantSkh = Plant::first();

        $employeeSeeds = [
            [
                'name' => 'Joko Susilo',
                'email' => 'joko.susilo@herbatech.com',
                'nik' => 'EMP-PROD-2026-001',
                'dept_id' => $deptProd->id,
                'golongan_code' => 'G-1',
                'shift_code' => 'SFT-1',
                'position' => 'Operator Ekstraksi Herbal',
                'employment_status' => 'permanent',
                'basic_salary' => 4500000,
                'allowance_fixed' => 500000,
                'tax_ptkp' => 'K/1',
            ],
            [
                'name' => 'Dr. Farhan Wicaksono, Apt.',
                'email' => 'farhan.rnd@herbatech.com',
                'nik' => 'EMP-RND-2026-002',
                'dept_id' => $deptRnd->id,
                'golongan_code' => 'G-4',
                'shift_code' => 'NON-SFT',
                'position' => 'Supervisor Formulasi Herbal',
                'employment_status' => 'permanent',
                'basic_salary' => 11500000,
                'allowance_fixed' => 1500000,
                'tax_ptkp' => 'K/2',
            ],
            [
                'name' => 'Siti Rahmawati, S.Si.',
                'email' => 'siti.qc@herbatech.com',
                'nik' => 'EMP-QC-2026-003',
                'dept_id' => $deptQc->id,
                'golongan_code' => 'G-3',
                'shift_code' => 'NON-SFT',
                'position' => 'Senior QC Lab Analyst',
                'employment_status' => 'permanent',
                'basic_salary' => 7200000,
                'allowance_fixed' => 800000,
                'tax_ptkp' => 'TK/0',
            ],
            [
                'name' => 'Hendra Prasetyo',
                'email' => 'hendra.scm@herbatech.com',
                'nik' => 'EMP-SCM-2026-004',
                'dept_id' => $deptScm->id,
                'golongan_code' => 'G-2',
                'shift_code' => 'SFT-1',
                'position' => 'Petugas Inbound & Karantina Gudang',
                'employment_status' => 'contract',
                'basic_salary' => 5200000,
                'allowance_fixed' => 600000,
                'tax_ptkp' => 'TK/1',
            ],
            [
                'name' => 'Maya Anggraini, S.E.',
                'email' => 'maya.fat@herbatech.com',
                'nik' => 'EMP-FAT-2026-005',
                'dept_id' => $deptFat->id,
                'golongan_code' => 'G-3',
                'shift_code' => 'NON-SFT',
                'position' => 'Staff Tax & Payroll Accounting',
                'employment_status' => 'permanent',
                'basic_salary' => 6800000,
                'allowance_fixed' => 700000,
                'tax_ptkp' => 'TK/0',
            ],
            [
                'name' => 'Budi Santoso, S.Psi.',
                'email' => 'budi.hr@herbatech.com',
                'nik' => 'EMP-HR-2026-006',
                'dept_id' => $deptHr->id,
                'golongan_code' => 'G-4',
                'shift_code' => 'NON-SFT',
                'position' => 'HR Operations Supervisor',
                'employment_status' => 'permanent',
                'basic_salary' => 10000000,
                'allowance_fixed' => 1200000,
                'tax_ptkp' => 'K/1',
            ],
        ];

        $employeeModels = [];
        foreach ($employeeSeeds as $es) {
            $user = User::updateOrCreate(
                ['email' => $es['email']],
                [
                    'name' => $es['name'],
                    'password' => Hash::make('password123'),
                    'department_id' => $es['dept_id'],
                    'status' => 'active',
                ]
            );

            $empStatusMap = [
                'permanent' => 'pkwtt',
                'contract' => 'pkwt',
                'probation' => 'probation',
                'internship' => 'intern',
            ];

            $emp = HrisEmployee::updateOrCreate(
                ['nik' => $es['nik']],
                [
                    'user_id' => $user->id,
                    'department_id' => $es['dept_id'],
                    'plant_id' => $plantSkh?->id,
                    'grade_id' => $golModels[$es['golongan_code']]->id,
                    'position' => $es['position'],
                    'employment_status' => $empStatusMap[$es['employment_status']] ?? 'pkwtt',
                    'join_date' => '2025-01-15',
                    'status' => 'active',
                    'basic_salary' => $es['basic_salary'],
                    'bank_name' => 'BCA',
                    'bank_account_no' => '7820'.rand(100000, 999999),
                    'custom_attributes' => [
                        'allowance_fixed' => $es['allowance_fixed'],
                        'tax_ptkp_status' => $es['tax_ptkp'],
                        'leave_balance' => rand(8, 12),
                        'shift_id' => $shiftModels[$es['shift_code']]->id,
                        'office_location_id' => $locModels['Pabrik Herbatech Sukoharjo Plant']->id,
                    ],
                ]
            );

            $employeeModels[] = $emp;

            // Initialize Time Bank
            HrisTimeBank::updateOrCreate(
                [
                    'employee_id' => $emp->id,
                    'transaction_type' => 'deposit',
                ],
                [
                    'hours' => 12.0,
                    'balance_after' => 12.0,
                    'reference_type' => 'overtime',
                    'notes' => 'Deposit jam lembur awal',
                    'created_by' => 1,
                ]
            );
        }

        // 6. Sample Daily Attendances (Today & Yesterday)
        $today = Carbon::today()->toDateString();
        $yesterday = Carbon::yesterday()->toDateString();

        foreach ($employeeModels as $idx => $emp) {
            $isLate = $idx === 1; // Dr. Farhan terlambat 15 menit
            $checkInHour = $isLate ? '08:15:00' : '07:55:00';

            HrisAttendance::updateOrCreate(
                ['employee_id' => $emp->id, 'date' => $today],
                [
                    'shift_id' => $emp->shift_id,
                    'office_location_id' => $emp->office_location_id,
                    'check_in_time' => $checkInHour,
                    'check_in_latitude' => -7.681240,
                    'check_in_longitude' => 110.835420,
                    'check_out_time' => '17:05:00',
                    'late_minutes' => $isLate ? 15 : 0,
                    'work_hours' => 8.5,
                    'status' => $isLate ? 'late' : 'present',
                    'custom_attributes' => [
                        'distance_meters' => rand(15, 45),
                        'is_geofence_valid' => true,
                    ],
                ]
            );

            // Yesterday record
            HrisAttendance::updateOrCreate(
                ['employee_id' => $emp->id, 'date' => $yesterday],
                [
                    'shift_id' => $emp->shift_id,
                    'office_location_id' => $emp->office_location_id,
                    'check_in_time' => '07:50:00',
                    'check_in_latitude' => -7.681240,
                    'check_in_longitude' => 110.835420,
                    'check_out_time' => '17:00:00',
                    'late_minutes' => 0,
                    'work_hours' => 8.0,
                    'status' => 'present',
                    'custom_attributes' => [
                        'distance_meters' => 20,
                        'is_geofence_valid' => true,
                    ],
                ]
            );
        }

        // 7. Sample Leaves (Approved & Pending)
        HrisLeave::updateOrCreate(
            ['leave_number' => 'LEV-2026-0001'],
            [
                'employee_id' => $employeeModels[0]->id, // Joko Susilo
                'leave_category_id' => $catModels['LV-ANNUAL']->id,
                'start_date' => Carbon::now()->addDays(5)->toDateString(),
                'end_date' => Carbon::now()->addDays(7)->toDateString(),
                'total_days' => 3,
                'reason' => 'Acara syukuran keluarga di Boyolali',
                'emergency_phone' => '081234567890',
                'substitute_employee_id' => $employeeModels[3]->id,
                'status' => 'pending_spv',
            ]
        );

        HrisLeave::updateOrCreate(
            ['leave_number' => 'LEV-2026-0002'],
            [
                'employee_id' => $employeeModels[2]->id, // Siti Rahmawati
                'leave_category_id' => $catModels['LV-ANNUAL']->id,
                'start_date' => Carbon::now()->subDays(10)->toDateString(),
                'end_date' => Carbon::now()->subDays(9)->toDateString(),
                'total_days' => 2,
                'reason' => 'Perpanjangan paspor dan urusan keluarga',
                'emergency_phone' => '081987654321',
                'status' => 'approved',
                'approved_by' => 1,
                'approved_at' => Carbon::now()->subDays(11),
            ]
        );

        // 8. Sample Overtime SPL
        HrisOvertime::updateOrCreate(
            ['spl_number' => 'SPL-2026-0001'],
            [
                'employee_id' => $employeeModels[0]->id, // Joko
                'date' => $today,
                'start_time' => '17:00',
                'end_time' => '21:00',
                'total_hours' => 4.0,
                'multiplier_hours' => 7.5, // 1h x 1.5 + 3h x 2.0 = 7.5h
                'rate_per_hour' => 28901,
                'total_overtime_pay' => 216758,
                'is_holiday' => false,
                'compensation_type' => 'cash',
                'reason' => 'Target kejar batch kapsul meniran 40.000 blister',
                'work_target' => 'Ekstraksi 200kg simplisia & filtrasi',
                'status' => 'pending_spv',
            ]
        );

        HrisOvertime::updateOrCreate(
            ['spl_number' => 'SPL-2026-0002'],
            [
                'employee_id' => $employeeModels[3]->id, // Hendra SCM
                'date' => $yesterday,
                'start_time' => '17:00',
                'end_time' => '20:00',
                'total_hours' => 3.0,
                'multiplier_hours' => 5.5,
                'rate_per_hour' => 33526,
                'total_overtime_pay' => 184393,
                'is_holiday' => false,
                'compensation_type' => 'time_bank',
                'reason' => 'Penerimaan inbound truk 10 ton simplisia temulawak dari Wonogiri',
                'work_target' => 'Bongkar muat & sampling karantina',
                'status' => 'approved',
                'approved_by' => 1,
                'approved_at' => Carbon::now()->subDay(),
            ]
        );

        // 9. Sample Kaizen Kizuki Proposals
        $kizukis = [
            [
                'kizuki_number' => 'KIZ-2026-001',
                'employee_id' => $employeeModels[0]->id,
                'department_id' => $deptProd->id,
                'title' => 'Modifikasi Corong Chute Mesin Kapsulasi Meniran',
                'category' => 'quality',
                'current_condition' => 'Serbuk meniran halus sering beterbangan di sekitar corong hopper pengisian, menimbulkan debu di area ruang kelas E.',
                'proposed_idea' => 'Memasang cincin penutup silikon kedap udara (food grade) dan mini dust collector lokal di leher hopper.',
                'expected_benefit' => 'Mengurangi ceceran debu bahan aktif 85%, menurunkan risiko kontaminasi silang CPOTB, serta menghemat waktu sanitasi ruang 25 menit per batch.',
                'quality_score' => 90,
                'cost_saving_score' => 85,
                'delivery_score' => 80,
                'safety_score' => 95,
                'morale_score' => 85,
                'reward_points' => 75,
                'status' => 'implemented',
            ],
            [
                'kizuki_number' => 'KIZ-2026-002',
                'employee_id' => $employeeModels[3]->id,
                'department_id' => $deptScm->id,
                'title' => 'Implementasi Pallet Staging Berwarna di Gudang Karantina',
                'category' => '5s',
                'current_condition' => 'Pallet bahan baku simplisia baru masuk sering bercampur dengan bahan yang sudah lulus uji QC lab.',
                'proposed_idea' => 'Memberi kode strip warna neon di tepi pallet (Kuning = Karantina, Hijau = Released QC, Merah = Reject).',
                'expected_benefit' => 'Visual control seketika, mencegah salah ambil bahan baku ke ruang produksi 100%.',
                'quality_score' => 95,
                'cost_saving_score' => 80,
                'delivery_score' => 90,
                'safety_score' => 85,
                'morale_score' => 90,
                'reward_points' => 60,
                'status' => 'implemented',
            ],
            [
                'kizuki_number' => 'KIZ-2026-003',
                'employee_id' => $employeeModels[1]->id,
                'department_id' => $deptRnd->id,
                'title' => 'Otomasi Template Formulasi Presisi 100% via Spreadsheet ERP',
                'category' => 'productivity',
                'current_condition' => 'Kalkulasi batch size 100kg ke 500kg masih diketik manual di kalkulator sebelum input ke sistem.',
                'proposed_idea' => 'Menerapkan formula auto-scale terintegrasi dengan BOM modul R&D di sistem ERP.',
                'expected_benefit' => 'Mereduksi potensi human error penimbangan 100% dan efisiensi waktu 45 menit per formula.',
                'quality_score' => 85,
                'cost_saving_score' => 75,
                'delivery_score' => 85,
                'safety_score' => 80,
                'morale_score' => 85,
                'reward_points' => 50,
                'status' => 'under_review',
            ],
        ];

        foreach ($kizukis as $k) {
            HrisKizuki::updateOrCreate(['kizuki_number' => $k['kizuki_number']], $k);
        }

        // 10. Sample Monthly Payroll (Payslips)
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        foreach ($employeeModels as $idx => $emp) {
            $basic = (float) $emp->basic_salary;
            $allowance = (float) $emp->allowance_fixed;
            $attAllowance = 400000;
            $overtime = $idx === 0 ? 216758 : ($idx === 3 ? 184393 : 0);
            $gross = $basic + $allowance + $attAllowance + $overtime;

            // BPJS & TER calculation
            $bpjsTk = round($basic * 0.03, 0); // 3%
            $bpjsKes = round(min($basic, 12000000) * 0.01, 0); // 1%
            $lateDeduction = $idx === 1 ? 50000 : 0;

            // Simple TER estimation
            $terRate = 0.015; // 1.5%
            $pph21 = round($gross * $terRate, 0);
            $deductions = $bpjsTk + $bpjsKes + $pph21 + $lateDeduction;
            $net = $gross - $deductions;

            HrisSalary::updateOrCreate(
                [
                    'employee_id' => $emp->id,
                    'period_month' => $month,
                    'period_year' => $year,
                ],
                [
                    'salary_number' => sprintf('SAL-%d%02d-%04d', $year, $month, $emp->id),
                    'basic_salary' => $basic,
                    'fixed_allowances' => $allowance,
                    'attendance_allowances' => $attAllowance,
                    'overtime_pay' => $overtime,
                    'bpjs_tk_deduction' => $bpjsTk,
                    'bpjs_kes_deduction' => $bpjsKes,
                    'pph21_ter_deduction' => $pph21,
                    'late_deductions' => $lateDeduction,
                    'net_salary' => $net,
                    'payment_status' => 'approved',
                    'created_by' => 1,
                ]
            );
        }

        // 11. Strategic Performance BSC Level 3 & Level 4
        $bscObjectives = [
            [
                'code' => 'OBJ-PROD-01',
                'dept_id' => $deptProd->id,
                'period_year' => $year,
                'perspective' => 'internal_process',
                'name' => 'Zero Major Deviation pada Batch Ekstraksi & Pengolahan Herbal',
                'target_value' => 0,
                'actual_value' => 0,
                'polarity' => 'minimize',
                'weight' => 25,
                'achievement_percentage' => 100,
                'is_locked' => true,
                'programs' => [
                    ['program_name' => 'Kalibrasi Berkala Sensor Suhu & Tekanan Vakum Evaporator', 'progress_percentage' => 90],
                    ['program_name' => 'Pelatihan Sanitasi Mesin Sesuai Standar CPOTB 2026', 'progress_percentage' => 100],
                ],
            ],
            [
                'code' => 'OBJ-HR-01',
                'dept_id' => $deptHr->id,
                'period_year' => $year,
                'perspective' => 'learning_growth',
                'name' => 'Tingkat Kepatuhan Jam Kerja (Attendance On-Time Rate)',
                'target_value' => 98.0,
                'actual_value' => 96.8,
                'polarity' => 'maximize',
                'weight' => 20,
                'achievement_percentage' => 96.8,
                'is_locked' => false,
                'programs' => [
                    ['program_name' => 'Sosialisasi Sistem Geofencing Mobile Presensi', 'progress_percentage' => 85],
                    ['program_name' => 'Program Reward Bulanan Hadir 100% Tanpa Keterlambatan', 'progress_percentage' => 70],
                ],
            ],
            [
                'code' => 'OBJ-QC-01',
                'dept_id' => $deptQc->id,
                'period_year' => $year,
                'perspective' => 'customer',
                'name' => 'Kecepatan Rilis Uji Lab Karantina Bahan Baku (Lead Time CoA)',
                'target_value' => 3.0,
                'actual_value' => 2.8,
                'polarity' => 'minimize',
                'weight' => 25,
                'achievement_percentage' => 95.0,
                'is_locked' => true,
                'programs' => [
                    ['program_name' => 'Optimasi Metode Uji Kadar Sari Larut Air & Etanol Ekstrak', 'progress_percentage' => 95],
                ],
            ],
            [
                'code' => 'OBJ-FAT-01',
                'dept_id' => $deptFat->id,
                'period_year' => $year,
                'perspective' => 'financial',
                'name' => 'Efisiensi Anggaran Lembur vs Output Produksi (Overtime Budget Control)',
                'target_value' => 95.0,
                'actual_value' => 93.5,
                'polarity' => 'minimize',
                'weight' => 30,
                'achievement_percentage' => 98.2,
                'is_locked' => false,
                'programs' => [
                    ['program_name' => 'Audit Mingguan Realisasi Jam SPL Lintas Shift', 'progress_percentage' => 80],
                ],
            ],
        ];

        foreach ($bscObjectives as $bo) {
            $programs = $bo['programs'];

            $obj = HrisDepartmentObjective::updateOrCreate(
                ['code' => $bo['code']],
                [
                    'department_id' => $bo['dept_id'],
                    'name' => $bo['name'],
                    'polarity' => $bo['polarity'],
                    'weight' => $bo['weight'],
                    'target_formula' => $bo['perspective'],
                ]
            );

            // Record monthly achievement
            HrisDepartmentObjectiveAchievement::updateOrCreate(
                [
                    'department_objective_id' => $obj->id,
                    'period_month' => $month,
                    'period_year' => $year,
                ],
                [
                    'target_value' => $bo['target_value'],
                    'actual_value' => $bo['actual_value'],
                    'achievement_percentage' => $bo['achievement_percentage'],
                    'status' => $bo['is_locked'] ? 'locked' : 'approved',
                ]
            );

            foreach ($programs as $prog) {
                HrisDepartmentWorkProgram::updateOrCreate(
                    [
                        'department_id' => $bo['dept_id'],
                        'objective_id' => $obj->id,
                        'program_name' => $prog['program_name'],
                    ],
                    [
                        'progress_percentage' => $prog['progress_percentage'],
                        'status' => $prog['progress_percentage'] >= 100 ? 'completed' : 'in_progress',
                    ]
                );
            }
        }
    }
}
