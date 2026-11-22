<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Presensi & Kehadiran Canggih
        Schema::create('hris_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hris_employees')->cascadeOnDelete();
            $table->foreignId('schedule_id')->nullable()->constrained('hris_employee_schedules')->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('hris_shifts')->nullOnDelete();
            $table->foreignId('office_location_id')->nullable()->constrained('hris_office_locations')->nullOnDelete();
            $table->date('date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->decimal('check_in_latitude', 10, 7)->nullable();
            $table->decimal('check_in_longitude', 10, 7)->nullable();
            $table->boolean('check_in_face_verified')->default(true);
            $table->string('check_in_photo_path')->nullable();
            $table->unsignedSmallInteger('late_minutes')->default(0);
            $table->unsignedSmallInteger('early_leave_minutes')->default(0);
            $table->decimal('work_hours', 5, 2)->default(0);
            $table->enum('status', ['present', 'late', 'leave', 'alpha', 'holiday'])->default('present');
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index(['date', 'status']);
        });

        // 2. Presensi Ibadah Spiritual
        Schema::create('hris_prayer_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hris_employees')->cascadeOnDelete();
            $table->date('date');
            $table->enum('prayer_name', ['subuh', 'dzuhur', 'ashar', 'maghrib', 'isya'])->default('dzuhur');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['present', 'absent'])->default('present');
            $table->timestamps();

            $table->unique(['employee_id', 'date', 'prayer_name']);
        });

        // 3. Kategori Cuti
        Schema::create('hris_leave_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. ANNUAL, SICK, MATERNITY, SPECIAL
            $table->string('name');
            $table->unsignedSmallInteger('default_quota')->default(12);
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_attachment')->default(false);
            $table->timestamps();
        });

        // 4. Pengajuan Cuti (Leaves)
        Schema::create('hris_leaves', function (Blueprint $table) {
            $table->id();
            $table->string('leave_number')->unique(); // LEV-YYYYMM-XXXX
            $table->foreignId('employee_id')->constrained('hris_employees')->cascadeOnDelete();
            $table->foreignId('leave_category_id')->constrained('hris_leave_categories')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('total_days')->default(1);
            $table->text('reason');
            $table->string('attachment_path')->nullable();
            $table->enum('status', ['pending_spv', 'pending_hr', 'approved', 'rejected'])->default('pending_spv');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
        });

        // 5. Surat Perintah Lembur (SPL Overtime)
        Schema::create('hris_overtimes', function (Blueprint $table) {
            $table->id();
            $table->string('spl_number')->unique(); // SPL-YYYYMM-XXXX
            $table->foreignId('employee_id')->constrained('hris_employees')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->decimal('total_hours', 5, 2);
            $table->decimal('calculated_hours', 5, 2)->default(0); // Depnaker Multiplier (1.5x, 2x, etc.)
            $table->text('reason');
            $table->enum('compensation_type', ['paid', 'timebank'])->default('paid');
            $table->enum('status', ['pending_spv', 'pending_om', 'pending_gm', 'approved', 'rejected'])->default('pending_spv');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
        });

        // 6. Time Bank System
        Schema::create('hris_time_banks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('hris_employees')->cascadeOnDelete();
            $table->enum('transaction_type', ['deposit', 'debt', 'debt_payment', 'withdraw']);
            $table->decimal('hours', 5, 2);
            $table->decimal('balance_after', 6, 2);
            $table->string('reference_type')->nullable(); // overtime, late_arrival, early_leave
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['employee_id', 'transaction_type']);
        });

        // 7. Kaizen Kizuki (Continuous Improvement & Gamification)
        Schema::create('hris_kizukis', function (Blueprint $table) {
            $table->id();
            $table->string('kizuki_number')->unique(); // KIZ-YYYYMM-XXXX
            $table->foreignId('employee_id')->constrained('hris_employees')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('title');
            $table->enum('category', ['safety', '5s', 'quality', 'cost', 'productivity'])->default('productivity');
            $table->text('current_condition');
            $table->text('proposed_improvement');
            $table->text('expected_impact')->nullable();
            $table->enum('status', ['draft', 'submitted', 'under_review', 'approved', 'implemented', 'rejected'])->default('submitted');
            $table->unsignedSmallInteger('score')->default(0); // 0-100 evaluation score
            $table->unsignedInteger('points_rewarded')->default(0);
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['department_id', 'category']);
        });

        // 8. Penggajian & Kompensasi (Payroll Engine)
        Schema::create('hris_salaries', function (Blueprint $table) {
            $table->id();
            $table->string('salary_number')->unique(); // SAL-YYYYMM-XXXX
            $table->foreignId('employee_id')->constrained('hris_employees')->cascadeOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('fixed_allowances', 15, 2)->default(0); // Tunjangan Jabatan & Masa Kerja
            $table->decimal('attendance_allowances', 15, 2)->default(0); // Uang Makan & Transport riil
            $table->decimal('overtime_pay', 15, 2)->default(0);
            $table->decimal('late_deductions', 15, 2)->default(0);
            $table->decimal('unpaid_leave_deductions', 15, 2)->default(0);
            $table->decimal('bpjs_tk_deduction', 15, 2)->default(0);
            $table->decimal('bpjs_kes_deduction', 15, 2)->default(0);
            $table->decimal('pph21_ter_deduction', 15, 2)->default(0); // Tarif Efektif Rata-Rata
            $table->decimal('net_salary', 15, 2);
            $table->enum('payment_status', ['draft', 'pending_approval', 'approved', 'paid'])->default('approved');
            $table->date('payment_date')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['employee_id', 'period_month', 'period_year']);
            $table->index(['period_year', 'period_month', 'payment_status']);
        });

        // 9. Penilaian Kinerja Individu (KPI)
        Schema::create('hris_kpis', function (Blueprint $table) {
            $table->id();
            $table->string('kpi_number')->unique(); // KPI-YYYY-S1-XXXX
            $table->foreignId('employee_id')->constrained('hris_employees')->cascadeOnDelete();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_semester')->default(1);
            $table->decimal('total_score', 5, 2)->default(0); // 0-100
            $table->enum('grade', ['A', 'B', 'C', 'D'])->default('B');
            $table->enum('status', ['draft', 'evaluated', 'approved'])->default('evaluated');
            $table->foreignId('evaluator_id')->constrained('users');
            $table->text('feedback')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'period_year', 'period_semester']);
        });

        Schema::create('hris_kpi_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kpi_id')->constrained('hris_kpis')->cascadeOnDelete();
            $table->string('indicator_name');
            $table->decimal('weight', 5, 2); // e.g. 25%
            $table->decimal('target', 10, 2);
            $table->decimal('actual', 10, 2);
            $table->decimal('score', 5, 2);
            $table->decimal('weighted_score', 5, 2);
            $table->timestamps();
        });

        // 10. Strategic Performance (BSC Level 3 & Level 4 Engine)
        Schema::create('hris_department_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('code')->unique(); // e.g. OBJ-PROD-01
            $table->string('name');
            $table->enum('polarity', ['maximize', 'minimize'])->default('maximize');
            $table->decimal('weight', 5, 2)->default(10);
            $table->string('target_formula')->nullable();
            $table->timestamps();
        });

        Schema::create('hris_department_objective_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_objective_id')->constrained('hris_department_objectives', 'id', 'hris_obj_ach_fk')->cascadeOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->decimal('target_value', 12, 2);
            $table->decimal('actual_value', 12, 2);
            $table->decimal('achievement_percentage', 5, 2);
            $table->enum('status', ['draft', 'approved', 'locked', 'pushed_to_bsc'])->default('approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['department_objective_id', 'period_year', 'period_month'], 'hris_obj_ach_idx');
        });

        Schema::create('hris_department_work_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('objective_id')->nullable()->constrained('hris_department_objectives', 'id', 'hris_prog_obj_fk')->nullOnDelete();
            $table->string('program_name');
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->enum('status', ['planned', 'in_progress', 'completed', 'delayed'])->default('in_progress');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hris_department_work_programs');
        Schema::dropIfExists('hris_department_objective_achievements');
        Schema::dropIfExists('hris_department_objectives');
        Schema::dropIfExists('hris_kpi_items');
        Schema::dropIfExists('hris_kpis');
        Schema::dropIfExists('hris_salaries');
        Schema::dropIfExists('hris_kizukis');
        Schema::dropIfExists('hris_time_banks');
        Schema::dropIfExists('hris_overtimes');
        Schema::dropIfExists('hris_leaves');
        Schema::dropIfExists('hris_leave_categories');
        Schema::dropIfExists('hris_prayer_attendances');
        Schema::dropIfExists('hris_attendances');
    }
};
