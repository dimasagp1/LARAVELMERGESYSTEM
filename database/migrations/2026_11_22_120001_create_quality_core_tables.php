<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Direktori Hierarki Folder Dokumen Mutu QMS
        Schema::create('quality_document_folders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('quality_document_folders')->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->default('#0d9488');
            $table->string('icon')->default('Folder');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Master Induk Dokumen Mutu (Piramida 4-Tingkat CPOB/ISO)
        Schema::create('quality_master_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('folder_id')->nullable()->constrained('quality_document_folders')->nullOnDelete();
            $table->unsignedTinyInteger('level')->default(2); // 1: MM, 2: SOP, 3: IK, 4: FR
            $table->string('document_code'); // MM, SOP, IK, FR
            $table->string('document_number')->unique(); // e.g. SOP-QA-001
            $table->string('title');
            $table->text('scope')->nullable(); // 12 Ruang Lingkup CPOB
            $table->string('department')->default('QA');
            $table->json('document_links')->nullable(); // [{label, url}]
            $table->string('tempat_penyimpanan')->nullable(); // e.g. Lemari Arsip QA 01 Rak B
            $table->string('no_perubahan_cc')->nullable(); // Ref to Change Request
            $table->enum('status', ['Active', 'Obsolete'])->default('Active');
            $table->date('effective_date')->nullable();
            $table->string('revision')->default('00');
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Logbook Riwayat Revisi Dokumen Mutu
        Schema::create('quality_document_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_document_id')->constrained('quality_master_documents')->cascadeOnDelete();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('revision');
            $table->string('title');
            $table->date('effective_date');
            $table->text('change_notes')->nullable();
            $table->timestamps();
        });

        // 4. Pengaturan Sistem QMS (Settings / Default Options)
        Schema::create('quality_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        // Compatibility Views for Direct ERD table names
        try {
            DB::statement('CREATE VIEW document_folders AS SELECT * FROM quality_document_folders');
            DB::statement('CREATE VIEW master_documents AS SELECT * FROM quality_master_documents');
            DB::statement('CREATE VIEW document_revisions AS SELECT * FROM quality_document_revisions');
        } catch (Throwable $e) {
            // Ignore if view creation is unsupported or fails in specific sandbox mode
        }
    }

    public function down(): void
    {
        try {
            DB::statement('DROP VIEW IF EXISTS document_revisions');
            DB::statement('DROP VIEW IF EXISTS master_documents');
            DB::statement('DROP VIEW IF EXISTS document_folders');
        } catch (Throwable $e) {
        }

        Schema::dropIfExists('quality_settings');
        Schema::dropIfExists('quality_document_revisions');
        Schema::dropIfExists('quality_master_documents');
        Schema::dropIfExists('quality_document_folders');
    }
};
