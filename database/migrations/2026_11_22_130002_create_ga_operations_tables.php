<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Permintaan ATK & Rumah Tangga (Multi-Tier Approval & Granular Revision)
        Schema::create('ga_atk_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('ga_atk_requests')->cascadeOnDelete();
            $table->string('request_number')->unique(); // ATK-202611-0001, ATK-202611-0001-R1
            $table->enum('type', ['ATK', 'RT'])->default('ATK');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('department_id')->constrained('departments');
            $table->date('tanggal_pengajuan');
            $table->text('alasan_pengajuan')->nullable();
            $table->enum('status', [
                'DRAFT',
                'PENDING_MANAGER',
                'PENDING_ADMIN_GA',
                'APPROVED',
                'REJECTED',
                'HANDED_OVER',
            ])->default('PENDING_MANAGER');

            // Approval Level 1: Manager Divisi Pemohon
            $table->foreignId('manager_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('manager_approved_at')->nullable();
            $table->text('manager_notes')->nullable();

            // Approval Level 2: Admin GA / HRGA
            $table->foreignId('admin_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('admin_approved_at')->nullable();
            $table->text('admin_notes')->nullable();

            // Serah Terima Fisik Barang
            $table->date('diberikan_pada')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_signature')->nullable();

            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Rincian Item Permintaan ATK & RT (Granular Item-Level Approval)
        Schema::create('ga_atk_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('ga_atk_requests')->cascadeOnDelete();
            $table->foreignId('atk_item_id')->nullable()->constrained('ga_atk_items')->nullOnDelete();
            $table->string('custom_item_name')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->unsignedInteger('approved_quantity')->nullable();
            $table->foreignId('uom_id')->constrained('uoms');
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->string('rejection_note')->nullable();
            $table->timestamps();
        });

        // 3. Work Order GA (Form A - Request & Form B - Engineer Check)
        Schema::create('ga_work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number')->unique(); // WO-202611-0001
            $table->foreignId('user_id')->constrained('users'); // Pemohon Form A
            $table->foreignId('department_id')->constrained('departments');
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->text('complaint_description');
            $table->enum('priority', ['Rendah', 'Normal', 'Tinggi', 'Darurat'])->default('Normal');
            $table->enum('category', [
                'Gedung & Sipil',
                'Kelistrikan & AC',
                'Plumbing & Air',
                'Mesin & Utilitas',
                'Kendaraan',
                'Lainnya',
            ])->default('Gedung & Sipil');
            $table->string('attachment_path')->nullable();

            // Triage & Penugasan Teknisi
            $table->foreignId('assigned_engineer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('assigned_at')->nullable();

            // Form B: Hasil Pengecekan Lapangan oleh Teknisi
            $table->text('check_result')->nullable();
            $table->text('engineer_needs')->nullable();
            $table->boolean('needs_material')->default(false);
            $table->text('rejection_note')->nullable();

            $table->enum('status', [
                'Menunggu Penugasan',
                'Pengecekan Engineer',
                'Menunggu Pengadaan Material',
                'Sedang Dikerjakan',
                'Menunggu Serah Terima',
                'Selesai',
                'Ditolak',
            ])->default('Menunggu Penugasan');

            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Log Progres Pengerjaan Work Order (Timeline Pengerjaan Harian)
        Schema::create('ga_work_order_progresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('ga_work_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->text('description');
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });

        // 5. Fasilitas Penunjang Umum / Pengadaan Material (Form C - FPU Request)
        Schema::create('ga_fpu_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('ga_work_orders')->cascadeOnDelete();
            $table->string('fpu_number')->unique(); // FPU-202611-0001
            $table->date('request_date');
            $table->enum('priority', ['Sangat Penting', 'Penting', 'Biasa'])->default('Penting');
            $table->enum('execution_type', ['Internal', 'Eksternal'])->default('Internal');
            $table->enum('category', ['Pembuatan', 'Perbaikan', 'Pengadaan'])->default('Pengadaan');

            // Approval Berjenjang
            $table->boolean('verified_by_hrga')->default(false);
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('verified_at')->nullable();

            $table->boolean('known_by_manager')->default(false);
            $table->foreignId('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('manager_approved_at')->nullable();

            $table->boolean('approved_by_gm')->default(false);
            $table->foreignId('gm_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('gm_approved_at')->nullable();

            $table->enum('status', [
                'DRAFT',
                'PENDING_HRGA',
                'PENDING_MANAGER',
                'PENDING_GM',
                'APPROVED',
                'REJECTED',
            ])->default('PENDING_HRGA');

            $table->json('custom_attributes')->nullable();
            $table->timestamps();
        });

        // 6. Rincian Material Form C
        Schema::create('ga_fpu_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fpu_request_id')->constrained('ga_fpu_requests')->cascadeOnDelete();
            $table->string('material_name');
            $table->string('specification')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('uom')->default('Pcs');
            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->timestamps();
        });

        // 7. Berita Acara Serah Terima Pekerjaan (Form D - Handover & Rating)
        Schema::create('ga_wo_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('ga_work_orders')->cascadeOnDelete();
            $table->string('bast_number')->unique(); // BAST-202611-0001
            $table->date('handover_date');
            $table->foreignId('prepared_by_user_id')->constrained('users');
            $table->foreignId('client_user_id')->constrained('users');
            $table->unsignedTinyInteger('satisfaction_rating')->nullable(); // 1 - 5 Bintang
            $table->text('client_feedback')->nullable();
            $table->string('client_signature_path')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->dateTime('confirmed_at')->nullable();
            $table->timestamps();
        });

        // Compatibility Views for Direct ERD table names
        try {
            DB::statement('CREATE VIEW atk_requests AS SELECT * FROM ga_atk_requests');
            DB::statement('CREATE VIEW work_orders AS SELECT * FROM ga_work_orders');
            DB::statement('CREATE VIEW fpu_requests AS SELECT * FROM ga_fpu_requests');
            DB::statement('CREATE VIEW wo_handovers AS SELECT * FROM ga_wo_handovers');
        } catch (Throwable $e) {
            // Ignore in environments where views cannot be created
        }
    }

    public function down(): void
    {
        try {
            DB::statement('DROP VIEW IF EXISTS wo_handovers');
            DB::statement('DROP VIEW IF EXISTS fpu_requests');
            DB::statement('DROP VIEW IF EXISTS work_orders');
            DB::statement('DROP VIEW IF EXISTS atk_requests');
        } catch (Throwable $e) {
        }

        Schema::dropIfExists('ga_wo_handovers');
        Schema::dropIfExists('ga_fpu_materials');
        Schema::dropIfExists('ga_fpu_requests');
        Schema::dropIfExists('ga_work_order_progresses');
        Schema::dropIfExists('ga_work_orders');
        Schema::dropIfExists('ga_atk_request_items');
        Schema::dropIfExists('ga_atk_requests');
    }
};
