<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Master Shift Patroli Keamanan
        Schema::create('ga_security_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Pagi, Siang, Malam
            $table->time('start_time'); // 07:00:00
            $table->time('end_time'); // 15:00:00
            $table->timestamps();
        });

        // 2. Master Pos Checkpoint Keamanan
        Schema::create('ga_security_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // CP-SEC-01
            $table->string('name'); // Pos Gerbang Utama, Gardu Trafo, Loading Dock
            $table->string('location_description')->nullable();
            $table->foreignId('plant_id')->constrained('plants');
            $table->unsignedInteger('target_duration_minutes')->default(5);
            $table->string('qr_token')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Penugasan Shift Satpam
        Schema::create('ga_security_assignments', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('shift_id')->constrained('ga_security_shifts');
            $table->string('notes')->nullable();
            $table->timestamps();
        });

        // 4. Log Rekapitulasi Patroli Satpam (Mobile Scanner + Evident Report)
        Schema::create('ga_security_checkpoint_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkpoint_id')->constrained('ga_security_checkpoints');
            $table->foreignId('user_id')->constrained('users'); // Satpam
            $table->dateTime('scanned_at');
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->enum('status', [
                'Aman',
                'Pintu Belum Terkunci',
                'Lampu/AC Menyala',
                'Anomali / Insiden',
            ])->default('Aman');
            $table->boolean('is_anomaly')->default(false);
            $table->text('anomaly_notes')->nullable();
            $table->string('evidence_photo_path')->nullable();
            $table->timestamps();
        });

        // 5. Master Titik Sanitasi Kebersihan (Cleaning Checkpoints)
        Schema::create('ga_cleaning_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // CP-CLN-01
            $table->string('name'); // Toilet Lantai 1, Locker Room, Koridor Produksi
            $table->foreignId('plant_id')->constrained('plants');
            $table->string('qr_token')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 6. Log Pemeriksaan Sanitasi & Kebersihan
        Schema::create('ga_cleaning_checkpoint_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checkpoint_id')->constrained('ga_cleaning_checkpoints');
            $table->foreignId('user_id')->constrained('users'); // Petugas Cleaning
            $table->dateTime('scanned_at');
            $table->boolean('is_sanitized')->default(true);
            $table->json('checklist_json')->nullable(); // {floor: true, trash: true, soap: true}
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Compatibility Views for Direct ERD table names
        try {
            DB::statement('CREATE VIEW security_checkpoint_logs AS SELECT * FROM ga_security_checkpoint_logs');
            DB::statement('CREATE VIEW cleaning_checkpoint_logs AS SELECT * FROM ga_cleaning_checkpoint_logs');
        } catch (Throwable $e) {
            // Ignore in environments where views cannot be created
        }
    }

    public function down(): void
    {
        try {
            DB::statement('DROP VIEW IF EXISTS cleaning_checkpoint_logs');
            DB::statement('DROP VIEW IF EXISTS security_checkpoint_logs');
        } catch (Throwable $e) {
        }

        Schema::dropIfExists('ga_cleaning_checkpoint_logs');
        Schema::dropIfExists('ga_cleaning_checkpoints');
        Schema::dropIfExists('ga_security_checkpoint_logs');
        Schema::dropIfExists('ga_security_assignments');
        Schema::dropIfExists('ga_security_checkpoints');
        Schema::dropIfExists('ga_security_shifts');
    }
};
