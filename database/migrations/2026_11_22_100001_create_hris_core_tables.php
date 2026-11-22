<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Golongan & Grading
        Schema::create('hris_master_golongans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. G1, G2, G3, G4, G5
            $table->string('grade_name'); // e.g. Staff Muda, Specialist, Manager
            $table->unsignedTinyInteger('level')->default(1);
            $table->decimal('min_salary', 15, 2)->default(0);
            $table->decimal('max_salary', 15, 2)->default(0);
            $table->foreignId('next_grade_id')->nullable()->constrained('hris_master_golongans')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        // 2. Master Karyawan (Employees)
        Schema::create('hris_employees', function (Blueprint $table) {
            $table->id();
            $table->string('nik')->unique(); // NIK Karyawan e.g. HII-2026-001
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('plant_id')->nullable()->constrained('plants')->nullOnDelete();
            $table->string('position'); // Jabatan e.g. Senior Formulation Scientist
            $table->foreignId('grade_id')->nullable()->constrained('hris_master_golongans')->nullOnDelete();
            $table->date('join_date');
            $table->enum('employment_status', ['pkwtt', 'pkwt', 'probation', 'intern'])->default('pkwtt');
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_no')->nullable();
            $table->string('npwp')->nullable();
            $table->string('bpjs_tk_no')->nullable();
            $table->string('bpjs_kes_no')->nullable();
            $table->enum('status', ['active', 'inactive', 'resigned'])->default('active');
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['nik', 'status']);
            $table->index(['department_id', 'plant_id']);
        });

        // 3. Master Shift Kerja
        Schema::create('hris_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // SHIFT-1, SHIFT-2, NON-SHIFT
            $table->string('name'); // Shift Pagi (07:00 - 15:00)
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('late_tolerance_minutes')->default(15);
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->boolean('is_night_shift')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Geofencing Lokasi Kantor / Pabrik
        Schema::create('hris_office_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Pabrik Sukoharjo, Head Office
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->unsignedInteger('radius_meters')->default(100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Roster Penjadwalan Shift Karyawan
        Schema::create('hris_employee_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hris_employees')->cascadeOnDelete();
            $table->foreignId('shift_id')->constrained('hris_shifts')->cascadeOnDelete();
            $table->date('schedule_date');
            $table->string('status')->default('scheduled'); // scheduled, attended, absent, swapped
            $table->timestamps();

            $table->unique(['employee_id', 'schedule_date']);
            $table->index(['schedule_date', 'shift_id']);
        });

        // 6. Pengajuan Tukar Shift
        Schema::create('hris_shift_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // SFT-YYYYMM-XXXX
            $table->foreignId('employee_id')->constrained('hris_employees')->cascadeOnDelete();
            $table->foreignId('current_shift_id')->constrained('hris_shifts');
            $table->foreignId('requested_shift_id')->constrained('hris_shifts');
            $table->date('target_date');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hris_shift_requests');
        Schema::dropIfExists('hris_employee_schedules');
        Schema::dropIfExists('hris_office_locations');
        Schema::dropIfExists('hris_shifts');
        Schema::dropIfExists('hris_employees');
        Schema::dropIfExists('hris_master_golongans');
    }
};
