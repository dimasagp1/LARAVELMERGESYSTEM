<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Line Produksi (Jalur Perakitan / Pengolahan Pabrik)
        Schema::create('production_lines', function (Blueprint $table) {
            $table->id();
            $table->string('line_number')->unique(); // e.g. LINE-01, LINE-02
            $table->string('name'); // e.g. Lini Ekstraksi & Sirup Herbal, Lini Kapsul & Tablet
            $table->foreignId('plant_id')->nullable()->constrained('plants')->nullOnDelete();
            $table->unsignedInteger('design_capacity_hourly')->default(1000); // Unit per jam
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
        });

        // 2. Master Mesin Pabrik & Standar Cycle Time (CT)
        Schema::create('production_machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_id')->constrained('production_lines')->cascadeOnDelete();
            $table->string('machine_code')->unique(); // e.g. MSN-EXT-01, MSN-FL-02
            $table->string('name'); // e.g. Mesin Ekstraktor Vakum 500L, Rotary Filling & Capping
            $table->decimal('cycle_time_seconds', 8, 2)->default(1.20); // Standar CT dalam detik/unit
            $table->string('brand')->nullable();
            $table->string('model_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
        });

        // 3. Master Kamus Alasan Downtime (Kendala Mesin, Bahan, Vendor, Operasional)
        Schema::create('production_downtime_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. DT-MCH-01, DT-RM-01, DT-VND-01
            $table->enum('category', ['mesin', 'bahan', 'vendor', 'operasional'])->default('mesin');
            $table->string('name'); // e.g. Overheat Chiller, Menunggu Botol Primer, Briefing Shift
            $table->decimal('default_duration_minutes', 6, 2)->default(15.00);
            $table->boolean('requires_maintenance')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
        });

        // Compatibility Views for Direct ERD table names
        try {
            DB::statement('CREATE VIEW lines AS SELECT * FROM production_lines');
            DB::statement('CREATE VIEW mesins AS SELECT * FROM production_machines');
            DB::statement('CREATE VIEW alasan_downtimes AS SELECT * FROM production_downtime_reasons');
        } catch (Throwable $e) {
            // Ignore if view creation is unsupported or fails in specific sandbox mode
        }
    }

    public function down(): void
    {
        try {
            DB::statement('DROP VIEW IF EXISTS alasan_downtimes');
            DB::statement('DROP VIEW IF EXISTS mesins');
            DB::statement('DROP VIEW IF EXISTS lines');
        } catch (Throwable $e) {
            // ignore
        }

        Schema::dropIfExists('production_downtime_reasons');
        Schema::dropIfExists('production_machines');
        Schema::dropIfExists('production_lines');
    }
};
