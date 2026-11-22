<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Weekly Production Schedule (WPS) - PPIC Planning Engine
        Schema::create('weekly_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_number')->unique(); // e.g. WPS-202611-0001
            $table->foreignId('produk_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('line_id')->constrained('production_lines')->cascadeOnDelete();
            $table->enum('proses', ['mixing', 'filling', 'packing'])->default('mixing');
            $table->string('batch_number'); // e.g. EXT-2026-088
            $table->unsignedBigInteger('odoo_mo_id')->nullable();
            $table->string('mo_status')->default('confirmed'); // confirmed, done, cancelled
            $table->unsignedInteger('target_output')->default(20000); // Unit target
            $table->unsignedSmallInteger('mp_count')->default(10); // Man Power
            $table->unsignedInteger('multiplier')->default(2000); // Multiplier per MP
            $table->boolean('packing_hold')->default(false); // Penundaan packing tanpa memutus silsilah batch
            $table->date('tanggal');
            $table->enum('shift', ['shift1', 'shift2', 'shift3'])->default('shift1');
            $table->enum('status', ['draft', 'aktif', 'selesai', 'cancelled'])->default('aktif');
            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['produk_id', 'batch_number']);
            $table->index(['tanggal', 'shift']);
            $table->index(['line_id', 'proses', 'status']);
        });

        // 2. Laporan Harian Produksi (LHP) - Shop Floor Execution & Live Digital Timer
        Schema::create('laporan_harians', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique(); // e.g. LHP-202611-0001
            $table->foreignId('weekly_plan_id')->constrained('weekly_plans')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('products');
            $table->foreignId('line_id')->constrained('production_lines');
            $table->foreignId('mesin_id')->nullable()->constrained('production_machines')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users'); // Leader / Operator
            $table->date('tanggal');
            $table->string('shift')->default('shift1');
            $table->string('batch_number');
            $table->enum('proses', ['mixing', 'filling', 'packing']);
            $table->decimal('ct', 8, 2)->default(1.20); // Cycle Time Mesin (detik/unit)
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->dateTime('pause_started_at')->nullable();
            $table->integer('gross_time_menit')->default(0);
            $table->integer('waktu_bersih_menit')->default(0);
            $table->integer('total_pause_menit')->default(0);
            $table->enum('timer_status', ['draft', 'start', 'pause', 'end'])->default('draft');
            $table->integer('capacity_fisik')->default(0);
            $table->integer('output_fisik')->default(0);
            $table->integer('total_reject')->default(0);
            $table->decimal('oee_persen', 5, 2)->default(0.00);
            $table->decimal('yield_persen', 5, 2)->default(0.00);
            $table->decimal('availability_persen', 5, 2)->default(0.00);
            $table->decimal('performance_persen', 5, 2)->default(0.00);
            $table->decimal('produktivitas_persen', 5, 2)->default(0.00);
            $table->enum('status', ['draft', 'submitted', 'locked'])->default('draft');
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('locked_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['batch_number', 'proses']);
            $table->index(['tanggal', 'shift', 'status']);
            $table->index(['weekly_plan_id', 'status']);
        });

        // 3. Rincian Downtime Harian (Event Waktu Henti & Alasan)
        Schema::create('downtime_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_harian_id')->constrained('laporan_harians')->cascadeOnDelete();
            $table->foreignId('alasan_downtime_id')->nullable()->constrained('production_downtime_reasons')->nullOnDelete();
            $table->string('kategori')->default('mesin'); // mesin, bahan, vendor, operasional
            $table->string('alasan_nama')->nullable();
            $table->decimal('durasi_menit', 8, 2)->default(0.00);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['laporan_harian_id', 'kategori']);
        });

        // 4. Rincian Reject & Scrap Produk di Jalur Produksi
        Schema::create('reject_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laporan_harian_id')->constrained('laporan_harians')->cascadeOnDelete();
            $table->unsignedBigInteger('odoo_scrap_id')->nullable();
            $table->unsignedBigInteger('odoo_mo_id')->nullable();
            $table->string('jenis_reject'); // e.g. Segel Bocor, Volume Kurang, Partikel Asing
            $table->string('material_name')->nullable();
            $table->decimal('jumlah', 10, 2)->default(0.00);
            $table->string('satuan')->default('PCS');
            $table->decimal('biaya_satuan', 12, 2)->default(0.00);
            $table->decimal('total_nilai_scrap', 15, 2)->default(0.00);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['laporan_harian_id', 'jenis_reject']);
        });

        // Compatibility Views for Direct ERD table names
        try {
            DB::statement('CREATE VIEW production_weekly_plans AS SELECT * FROM weekly_plans');
            DB::statement('CREATE VIEW production_daily_reports AS SELECT * FROM laporan_harians');
            DB::statement('CREATE VIEW production_downtime_details AS SELECT * FROM downtime_details');
            DB::statement('CREATE VIEW production_reject_details AS SELECT * FROM reject_details');
        } catch (Throwable $e) {
            // ignore
        }
    }

    public function down(): void
    {
        try {
            DB::statement('DROP VIEW IF EXISTS production_reject_details');
            DB::statement('DROP VIEW IF EXISTS production_downtime_details');
            DB::statement('DROP VIEW IF EXISTS production_daily_reports');
            DB::statement('DROP VIEW IF EXISTS production_weekly_plans');
        } catch (Throwable $e) {
            // ignore
        }

        Schema::dropIfExists('reject_details');
        Schema::dropIfExists('downtime_details');
        Schema::dropIfExists('laporan_harians');
        Schema::dropIfExists('weekly_plans');
    }
};
