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
        // 1. PRF (Product Request Form)
        Schema::create('rnd_prfs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('target_market', 150)->nullable();
            $table->string('product_category', 100)->nullable();
            $table->text('concept')->nullable();
            $table->date('target_launch_date')->nullable();
            $table->json('attachments_json')->nullable();
            $table->string('status', 30)->default('submitted')->index(); // submitted, reviewed, approved, rejected
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'status']);
        });

        // 2. NPD Proposal
        Schema::create('rnd_npd_proposals', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->foreignId('prf_id')->nullable()->constrained('rnd_prfs')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->decimal('target_cogs', 16, 2)->default(0);
            $table->decimal('target_selling_price', 16, 2)->default(0);
            $table->date('development_start')->nullable();
            $table->date('development_end')->nullable();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('project_team_json')->nullable();
            $table->string('status', 30)->default('draft')->index(); // draft, in_progress, on_track, delayed, on_hold, completed
            $table->json('attachments_json')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'status']);
        });

        // 3. Preformulation Studies
        Schema::create('rnd_preformulation_studies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->foreignId('npd_proposal_id')->nullable()->constrained('rnd_npd_proposals')->nullOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('study_type', 50)->default('physicochemical'); // physicochemical, solubility, compatibility
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->foreignId('pic_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('results_notes')->nullable();
            $table->string('status', 30)->default('draft')->index(); // draft, pending_stage_1, pending_stage_2, approved, rejected
            $table->json('attachments_json')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. QbD (Quality by Design)
        Schema::create('rnd_qbds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->json('qtpp_json')->nullable(); // Quality Target Product Profile
            $table->json('cqa_json')->nullable();  // Critical Quality Attributes
            $table->json('cma_json')->nullable();  // Critical Material Attributes
            $table->json('cpp_json')->nullable();  // Critical Process Parameters
            $table->json('risk_analysis_json')->nullable(); // FMEA / Fishbone
            $table->string('status', 30)->default('draft')->index();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Formulasi Raw Material (RM)
        Schema::create('rnd_formulas', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('parent_formula_id')->nullable()->constrained('rnd_formulas')->nullOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->string('formula_type', 50)->default('new_product'); // new_product, existing, substitution
            $table->string('stage', 50)->default('lab_trial'); // product_form, lab_trial, sensory_test, plant_trial, market_test
            $table->decimal('target_serving_size_g', 12, 4)->default(2.0000); // e.g. 2 gram per sachet
            $table->string('serving_unit', 50)->default('sachet'); // sachet, kapsul, botol
            $table->unsignedInteger('servings_per_pack')->default(30); // 30 sachet per box
            $table->decimal('total_percentage', 8, 4)->default(100.0000); // Strict 100.00%
            $table->decimal('total_cost_per_kg', 16, 2)->default(0);
            $table->decimal('total_cost_per_pack', 16, 2)->default(0); // HPP RM
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('draft')->index(); // draft, pending_stage_1, pending_stage_2, approved, rejected
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_om')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by_gm')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_notes')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'status']);
            $table->index(['product_id', 'version']);
        });

        // 6. Formula Items (Komposisi Bahan Baku)
        Schema::create('rnd_formula_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('formula_id')->constrained('rnd_formulas')->cascadeOnDelete();
            $table->foreignId('material_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->decimal('percentage', 8, 4); // Wajib total 100.00%
            $table->decimal('weight_per_serving_g', 12, 4)->default(0);
            $table->decimal('price_per_kg', 16, 2)->default(0);
            $table->decimal('price_per_serving', 16, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['formula_id', 'material_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rnd_formula_items');
        Schema::dropIfExists('rnd_formulas');
        Schema::dropIfExists('rnd_qbds');
        Schema::dropIfExists('rnd_preformulation_studies');
        Schema::dropIfExists('rnd_npd_proposals');
        Schema::dropIfExists('rnd_prfs');
    }
};
