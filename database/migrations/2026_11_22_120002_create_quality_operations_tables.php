<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Change Request (CRA - Mayor / FMEA, CRB - Minor)
        Schema::create('quality_change_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('cr_number')->unique(); // CRA-2026-0001, CRB-2026-0001
            $table->enum('type', ['CRA', 'CRB'])->default('CRA');
            $table->string('sifat_perubahan'); // Formula, Bahan Awal, Proses, Fasilitas, Pengemas, Mesin, Dokumen
            $table->string('nama_produk'); // Nama Produk / Sistem / Mesin
            $table->text('awal_sebelum_perubahan');
            $table->text('usulan_perubahan');
            $table->text('alasan_perubahan')->nullable();
            $table->date('target_pelaksanaan')->nullable();

            // Parameter FMEA (Wajib untuk CRA)
            $table->unsignedInteger('severity')->default(1);
            $table->unsignedInteger('occurrence')->default(1);
            $table->unsignedInteger('detection')->default(1);
            $table->unsignedInteger('rpn')->default(1); // S * O * D
            $table->text('risk_mitigation')->nullable();

            // Verifikasi & Pengkajian Multi-Departemen (Stage 1 & Approvals)
            $table->json('qa_verification_data')->nullable();

            // Dokumentasi & Bukti Pelaksanaan Lapangan (Stage 2)
            $table->json('stage_2_data')->nullable();

            $table->enum('status', [
                'DRAFT',
                'OPEN',
                'IN REVIEW',
                'APPROVED',
                'IN PROGRESS',
                'COMPLETE',
                'REJECT',
            ])->default('DRAFT');

            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Deviation Reports (DR) & Penyelidikan Ketidaksesuaian (PK)
        Schema::create('quality_deviations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('initiator_id')->constrained('users')->cascadeOnDelete();
            $table->string('deviation_number')->unique(); // DR/001/2026
            $table->string('pk_number')->unique(); // PK/001/2026
            $table->string('topik');
            $table->string('department'); // Produksi, QC, RND, Supply Chain, dll
            $table->string('pic')->nullable();
            $table->string('nomor_bets')->nullable();
            $table->string('nomor_alat')->nullable();
            $table->string('nomor_dokumen')->nullable();
            $table->string('objek_terdampak')->nullable();
            $table->string('jenis_penyimpangan'); // Peralatan, Bahan, Proses, Dokumen, Lingkungan
            $table->enum('identifikasi_penyimpangan', ['Kritis', 'Mayor', 'Minor'])->default('Minor');
            $table->date('tanggal_temuan');

            // CPOB Compliance Checklists
            $table->boolean('is_other_batch_affected')->default(false);
            $table->text('other_batch_details')->nullable();
            $table->enum('frekuensi_kejadian', [
                'Tidak Pernah Sebelumnya',
                'Jarang',
                'Sering',
            ])->default('Tidak Pernah Sebelumnya');
            $table->boolean('is_production_stopped')->default(false);
            $table->text('immediate_action_details')->nullable();

            // Investigation Fields (6M Fishbone, 5-Why, FMEA Tindakan)
            $table->text('root_cause')->nullable();
            $table->json('attachments')->nullable(); // Dokumen / Foto Bukti Lapangan
            $table->json('fishbone_6m')->nullable(); // {machine: [], man: [], method: [], milieu: [], measurement: [], materials: []}
            $table->json('five_why')->nullable(); // ["Why 1", "Why 2", ...]
            $table->json('evaluasi_tindakan')->nullable(); // [{tindakan, severity, occurrence, detection, rpn, evaluasi}]
            $table->json('mengetahui_data')->nullable(); // [{user_id, name, role, feedback, feedback_at, signature_path}]
            $table->text('reject_reason')->nullable();

            $table->enum('status', [
                'DRAFT',
                'OPEN',
                'IN REVIEW',
                'APPROVED',
                'REJECTED',
            ])->default('DRAFT');

            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. CAPA (Corrective and Preventive Action) - Spawn Otomatis dari Deviation
        Schema::create('quality_capas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deviation_id')->constrained('quality_deviations')->cascadeOnDelete();
            $table->foreignId('initiator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('capa_number')->unique(); // CAPA/2026/001
            $table->string('deviation_number_ref'); // DR/001/2026
            $table->date('tanggal_penyimpangan');
            $table->text('tindakan_capa')->nullable();
            $table->string('bukti_lapangan_path')->nullable();
            $table->text('hasil_verifikasi_qa')->nullable();

            $table->enum('status', [
                'DRAFT',
                'IN PROGRESS',
                'APPROVED',
                'CLOSE',
                'REJECTED',
            ])->default('DRAFT');

            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. CAPA Sub-Actions (Multi-Tindakan Korektif & Preventif Lintas Departemen)
        Schema::create('quality_capa_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capa_id')->constrained('quality_capas')->cascadeOnDelete();
            $table->foreignId('pic_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action_number')->nullable(); // CAPA-S1-001
            $table->text('tindakan');
            $table->enum('jenis_tindakan', ['korektif', 'preventif'])->default('korektif');
            $table->string('department');
            $table->date('deadline');
            $table->enum('status', [
                'PENDING',
                'SUBMITTED',
                'REVISE',
                'VERIFIED',
            ])->default('PENDING');
            $table->string('bukti_file_path')->nullable();
            $table->text('catatan_pelaksanaan')->nullable();
            $table->dateTime('verified_at')->nullable();
            $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Compatibility Views for Direct ERD table names
        try {
            DB::statement('CREATE VIEW change_requests AS SELECT * FROM quality_change_requests');
            DB::statement('CREATE VIEW deviations AS SELECT * FROM quality_deviations');
            DB::statement('CREATE VIEW capas AS SELECT * FROM quality_capas');
            DB::statement('CREATE VIEW capa_actions AS SELECT * FROM quality_capa_actions');
        } catch (Throwable $e) {
            // Ignore if view creation is unsupported or fails in specific sandbox mode
        }
    }

    public function down(): void
    {
        try {
            DB::statement('DROP VIEW IF EXISTS capa_actions');
            DB::statement('DROP VIEW IF EXISTS capas');
            DB::statement('DROP VIEW IF EXISTS deviations');
            DB::statement('DROP VIEW IF EXISTS change_requests');
        } catch (Throwable $e) {
        }

        Schema::dropIfExists('quality_capa_actions');
        Schema::dropIfExists('quality_capas');
        Schema::dropIfExists('quality_deviations');
        Schema::dropIfExists('quality_change_requests');
    }
};
