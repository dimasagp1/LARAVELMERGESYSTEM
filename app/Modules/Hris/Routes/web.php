<?php

use App\Core\Controllers\ModuleFeatureController;
use App\Modules\Hris\Controllers\AttendanceController;
use App\Modules\Hris\Controllers\EmployeeController;
use App\Modules\Hris\Controllers\HrisController;
use App\Modules\Hris\Controllers\KizukiController;
use App\Modules\Hris\Controllers\LeaveController;
use App\Modules\Hris\Controllers\OvertimeController;
use App\Modules\Hris\Controllers\PayrollController;
use App\Modules\Hris\Controllers\StrategicBscController;
use Illuminate\Support\Facades\Route;

Route::prefix('hris')
    ->name('hris.')
    ->group(function () {
        // Overview Interactive Dashboard
        Route::get('/', [HrisController::class, 'index'])->name('index');

        // Master Employee & Organisasi
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{id}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::get('/employees/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{id}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::get('/departments', [ModuleFeatureController::class, 'render'])->name('departments');
        Route::get('/face-enrollment', [ModuleFeatureController::class, 'render'])->name('face-enrollment');
        Route::get('/profile-requests', [ModuleFeatureController::class, 'render'])->name('profile-requests');

        // Jadwal Kerja & Shift
        Route::get('/shifts', [ModuleFeatureController::class, 'render'])->name('shifts');
        Route::get('/schedules', [ModuleFeatureController::class, 'render'])->name('schedules');
        Route::get('/shift-requests', [ModuleFeatureController::class, 'render'])->name('shift-requests');
        Route::get('/holidays', [ModuleFeatureController::class, 'render'])->name('holidays');
        Route::get('/office-locations', [ModuleFeatureController::class, 'render'])->name('office-locations');

        // Attendance & Geofenced Check-In
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn'])->name('attendance.check-in');
        Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut'])->name('attendance.check-out');
        Route::get('/prayer-attendances', [ModuleFeatureController::class, 'render'])->name('prayer-attendances');
        Route::get('/late-arrival-requests', [ModuleFeatureController::class, 'render'])->name('late-arrival-requests');
        Route::get('/early-leave-requests', [ModuleFeatureController::class, 'render'])->name('early-leave-requests');
        Route::get('/sanctions', [ModuleFeatureController::class, 'render'])->name('sanctions');
        Route::get('/attendance-audit-logs', [ModuleFeatureController::class, 'render'])->name('attendance-audit-logs');

        // Leave & Permit Management
        Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
        Route::get('/leaves/create', [LeaveController::class, 'create'])->name('leaves.create');
        Route::post('/leaves', [LeaveController::class, 'store'])->name('leaves.store');
        Route::post('/leaves/{id}/approve', [LeaveController::class, 'approve'])->name('leaves.approve');
        Route::get('/leaves/{id}/pdf', [LeaveController::class, 'exportPdf'])->name('leaves.pdf');
        Route::get('/leave-categories', [ModuleFeatureController::class, 'render'])->name('leave-categories');
        Route::get('/bulk-day-off-events', [ModuleFeatureController::class, 'render'])->name('bulk-day-off-events');
        Route::get('/day-banks', [ModuleFeatureController::class, 'render'])->name('day-banks');

        // Overtime (SPL) & Time Bank
        Route::get('/overtime', [OvertimeController::class, 'index'])->name('overtime.index');
        Route::get('/overtime/create', [OvertimeController::class, 'create'])->name('overtime.create');
        Route::post('/overtime', [OvertimeController::class, 'store'])->name('overtime.store');
        Route::post('/overtime/{id}/approve', [OvertimeController::class, 'approve'])->name('overtime.approve');
        Route::get('/overtime/{id}/pdf', [OvertimeController::class, 'exportPdf'])->name('overtime.pdf');
        Route::get('/time-banks', [ModuleFeatureController::class, 'render'])->name('time-banks');

        // Kaizen Kizuki, Points, Trainings & MCU
        Route::get('/kizuki', [KizukiController::class, 'index'])->name('kizuki.index');
        Route::get('/kizuki/create', [KizukiController::class, 'create'])->name('kizuki.create');
        Route::post('/kizuki', [KizukiController::class, 'store'])->name('kizuki.store');
        Route::post('/kizuki/{id}/review', [KizukiController::class, 'review'])->name('kizuki.review');
        Route::get('/activities', [ModuleFeatureController::class, 'render'])->name('activities');
        Route::get('/points', [ModuleFeatureController::class, 'render'])->name('points');
        Route::get('/trainings', [ModuleFeatureController::class, 'render'])->name('trainings');
        Route::get('/training-evaluations', [ModuleFeatureController::class, 'render'])->name('training-evaluations');
        Route::get('/training-action-plans', [ModuleFeatureController::class, 'render'])->name('training-action-plans');
        Route::get('/medical-checkups', [ModuleFeatureController::class, 'render'])->name('medical-checkups');
        Route::get('/my-mcu', [ModuleFeatureController::class, 'render'])->name('my-mcu');

        // Kinerja & Payroll
        Route::get('/kpi-templates', [ModuleFeatureController::class, 'render'])->name('kpi-templates');
        Route::get('/kpis', [ModuleFeatureController::class, 'render'])->name('kpis');
        Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
        Route::post('/payroll/calculate', [PayrollController::class, 'calculate'])->name('payroll.calculate');
        Route::get('/payroll/{id}', [PayrollController::class, 'show'])->name('payroll.show');
        Route::get('/payroll/{id}/pdf', [PayrollController::class, 'exportPdf'])->name('payroll.pdf');
        Route::get('/payslips', [ModuleFeatureController::class, 'render'])->name('payslips');
        Route::get('/master-golongans', [ModuleFeatureController::class, 'render'])->name('master-golongans');
        Route::get('/remunerations', [ModuleFeatureController::class, 'render'])->name('remunerations');
        Route::get('/promotions', [ModuleFeatureController::class, 'render'])->name('promotions');

        // Strategic Performance BSC L3 & L4
        Route::get('/strategic', [StrategicBscController::class, 'index'])->name('strategic.index');
        Route::get('/strategic/objectives', [ModuleFeatureController::class, 'render'])->name('strategic.objectives');
        Route::get('/strategic/work-programs', [ModuleFeatureController::class, 'render'])->name('strategic.work-programs');
        Route::get('/strategic/finance-variables', [ModuleFeatureController::class, 'render'])->name('strategic.finance-variables');
        Route::get('/strategic/productivity-feeds', [ModuleFeatureController::class, 'render'])->name('strategic.productivity-feeds');
        Route::post('/strategic/objectives', [StrategicBscController::class, 'storeObjective'])->name('strategic.objectives.store');
        Route::post('/strategic/programs', [StrategicBscController::class, 'storeProgram'])->name('strategic.programs.store');
        Route::post('/strategic/objectives/{id}/evaluate', [StrategicBscController::class, 'evaluate'])->name('strategic.objectives.evaluate');
        Route::post('/strategic/objectives/{id}/push-to-bsc', [StrategicBscController::class, 'pushToBsc'])->name('strategic.objectives.push-to-bsc');

        // Fasilitas, SOP & AI
        Route::get('/work-orders', [ModuleFeatureController::class, 'render'])->name('work-orders');
        Route::get('/fpu-requests', [ModuleFeatureController::class, 'render'])->name('fpu-requests');
        Route::get('/wo-handovers', [ModuleFeatureController::class, 'render'])->name('wo-handovers');
        Route::get('/company-policies', [ModuleFeatureController::class, 'render'])->name('company-policies');
        Route::get('/manual-books', [ModuleFeatureController::class, 'render'])->name('manual-books');
        Route::get('/ai-assistant', [ModuleFeatureController::class, 'render'])->name('ai-assistant');
    });
