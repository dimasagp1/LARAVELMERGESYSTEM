<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Trial RM (Lab Testing)
        Schema::create('rnd_trial_rms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->foreignId('formula_id')->constrained('rnd_formulas')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('batch_qty_kg', 12, 4)->default(1.0000);
            $table->text('process_steps')->nullable();
            $table->string('decision', 30)->default('lulus'); // lulus, reformulasi
            $table->string('status', 30)->default('draft')->index(); // draft, pending_stage_1, pending_stage_2, approved, rejected
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_om')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_gm')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'status']);
        });

        // 2. Trial RM Verifications (Target vs Actual)
        Schema::create('rnd_trial_rm_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trial_rm_id')->constrained('rnd_trial_rms')->cascadeOnDelete();
            $table->string('parameter', 100); // pH, Viskositas, Rasa, Warna, Aroma, Berat Jenis, Homogenitas, Waktu Larut
            $table->string('target_spec', 200)->nullable();
            $table->string('actual_result', 200)->nullable();
            $table->boolean('is_compliant')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['trial_rm_id', 'parameter']);
        });

        // 3. Trial PM (Trial Packaging Material pada Mesin)
        Schema::create('rnd_trial_pms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->json('packaging_specs_json')->nullable(); // foil, thickness, dimensions, grammage
            $table->json('machine_params_json')->nullable();  // speed cpm, sealing temp, sealing pressure
            $table->string('leakage_test_result', 100)->nullable(); // Lolos / Bocor
            $table->string('seal_strength_result', 100)->nullable();
            $table->string('barcode_readability', 100)->nullable(); // Grade A / B / C
            $table->json('photos_json')->nullable();
            $table->string('status', 30)->default('draft')->index(); // draft, pending_approval, approved, rejected
            $table->foreignId('om_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('om_approved_at')->nullable();
            $table->text('rejection_notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'status']);
        });

        // 4. Trial PM 4-Departments Collective Consensus
        Schema::create('rnd_trial_pm_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trial_pm_id')->constrained('rnd_trial_pms')->cascadeOnDelete();
            $table->string('department_name', 50); // R&D, QC, Production, Engineering
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending'); // pending, approved, rejected
            $table->text('notes')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();

            $table->unique(['trial_pm_id', 'department_name']);
        });

        // 5. Log Book PM (Penerimaan Sampel Kemas)
        Schema::create('rnd_logbook_pms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trial_pm_id')->nullable()->constrained('rnd_trial_pms')->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->date('received_date');
            $table->string('supplier_sample_no', 100)->nullable();
            $table->string('material_name', 200);
            $table->decimal('qty_received', 12, 4)->default(0);
            $table->foreignId('uom_id')->nullable()->constrained('uoms')->nullOnDelete();
            $table->json('document_checklist_json')->nullable(); // surat_jalan, coa, spesifikasi
            $table->string('physical_condition', 100)->nullable();
            $table->string('status', 30)->default('on_going_trial')->index(); // on_going_trial, passed, rejected
            $table->foreignId('om_verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('om_verified_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['received_date', 'status']);
        });

        // 6. Sample Evaluation (Uji Sensori & Panelis)
        Schema::create('rnd_sample_evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('npd_proposal_id')->nullable()->constrained('rnd_npd_proposals')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('sample_code', 100);
            $table->date('session_date');
            $table->string('batch_code', 100)->nullable();
            $table->unsignedInteger('panelist_count')->default(15);
            $table->decimal('average_taste', 4, 2)->default(0);     // Scale 1-5 or 1-9
            $table->decimal('average_color', 4, 2)->default(0);
            $table->decimal('average_aroma', 4, 2)->default(0);
            $table->decimal('average_texture', 4, 2)->default(0);
            $table->decimal('average_aftertaste', 4, 2)->default(0);
            $table->decimal('overall_score', 4, 2)->default(0);
            $table->string('status', 30)->default('draft')->index(); // draft, in_progress, completed
            $table->text('conclusion')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. Stability Tests
        Schema::create('rnd_stability_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('test_type', 50)->default('accelerated'); // accelerated, real_time
            $table->string('condition', 100)->default('40°C ± 2°C / 75% RH ± 5% RH');
            $table->date('start_date');
            $table->string('batch_number', 100)->nullable();
            $table->json('intervals_json')->nullable(); // month 0, 1, 3, 6, 12, 24 test points
            $table->string('status', 30)->default('ongoing')->index(); // ongoing, compliant, failed
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 8. Formula Approval Form (Pengesahan Legal Formal sebelum BPOM)
        Schema::create('rnd_formula_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->foreignId('formula_id')->constrained('rnd_formulas')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('category', 100)->nullable();
            $table->string('dosage_form', 100)->nullable(); // Serbuk instan, kapsul, sirup
            $table->text('pack_specs')->nullable();
            $table->text('usage_directions')->nullable();
            $table->text('claims')->nullable();
            $table->foreignId('gm_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('gm_approved_at')->nullable();
            $table->string('status', 50)->default('pending')->index(); // pending, checked_gm, approved_direktur, approved, rejected
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 9. Technology Transfer
        Schema::create('rnd_technology_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('protocol_number', 100)->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('formula_id')->nullable()->constrained('rnd_formulas')->nullOnDelete();
            $table->foreignId('target_plant_id')->nullable()->constrained('plants')->nullOnDelete();
            $table->decimal('scale_up_batch_size', 16, 2)->default(0);
            $table->text('master_batch_record_summary')->nullable();
            $table->string('status', 30)->default('draft')->index(); // draft, in_progress, approved
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 10. NIE Approved (Badan POM RI)
        Schema::create('rnd_nie_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('nie_number', 100)->unique(); // e.g. POM TR243012341
            $table->string('registration_type', 50)->default('TR'); // TR, TI, SD, MD
            $table->date('issue_date');
            $table->date('expiry_date');
            $table->string('certificate_path')->nullable();
            $table->string('status', 30)->default('active')->index(); // active, expired, renewal_needed
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rnd_nie_approvals');
        Schema::dropIfExists('rnd_technology_transfers');
        Schema::dropIfExists('rnd_formula_approvals');
        Schema::dropIfExists('rnd_stability_tests');
        Schema::dropIfExists('rnd_sample_evaluations');
        Schema::dropIfExists('rnd_logbook_pms');
        Schema::dropIfExists('rnd_trial_pm_approvals');
        Schema::dropIfExists('rnd_trial_pms');
        Schema::dropIfExists('rnd_trial_rm_verifications');
        Schema::dropIfExists('rnd_trial_rms');
    }
};
