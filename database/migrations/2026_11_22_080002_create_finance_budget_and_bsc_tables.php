<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fin_budget_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // CAT_RAW, CAT_PKG, CAT_MAINT, CAT_UTIL, CAT_SALARY, CAT_ADMIN
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fin_monthly_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained('fin_fiscal_years')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('fin_periods')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('budget_category_id')->nullable()->constrained('fin_budget_categories')->nullOnDelete();
            $table->decimal('initial_budget', 15, 2)->default(0);
            $table->decimal('revised_budget', 15, 2)->default(0);
            $table->decimal('realized_amount', 15, 2)->default(0);
            $table->decimal('booked_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['period_id', 'department_id']);
        });

        Schema::create('fin_revenue_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained('fin_fiscal_years')->cascadeOnDelete();
            $table->unsignedTinyInteger('month'); // 1..12
            $table->decimal('target_revenue', 15, 2);
            $table->decimal('actual_revenue', 15, 2)->default(0);
            $table->decimal('target_units', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['fiscal_year_id', 'month']);
        });

        Schema::create('fin_production_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained('fin_fiscal_years')->cascadeOnDelete();
            $table->unsignedTinyInteger('month');
            $table->decimal('machine_capacity_hours', 8, 2);
            $table->decimal('target_yield_pct', 5, 2)->default(98.50);
            $table->decimal('bom_cost_budget', 15, 2);
            $table->decimal('labor_cost_budget', 15, 2);
            $table->timestamps();

            $table->unique(['fiscal_year_id', 'month']);
        });

        Schema::create('fin_dynamic_budgets', function (Blueprint $table) {
            $table->id();
            $table->string('account_code')->unique();
            $table->string('account_name');
            $table->decimal('fixed_amount', 15, 2)->default(0);
            $table->decimal('variable_rate', 8, 4)->default(0); // variable % of revenue basis
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fin_ratio_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('ratio_code')->unique(); // GPM, NPM, ROA, ROE, ITO, ATO, ARTO, DIO, DSO, DPO, PROD_EMP, PROD_HR, etc.
            $table->string('name');
            $table->enum('pillar', ['profitability', 'activity', 'productivity', 'liquidity', 'solvency']);
            $table->decimal('weight_pct', 5, 2); // 30%, 25%, 20%, 15%, 10%
            $table->decimal('target_value', 10, 2);
            $table->enum('polarity', ['higher_is_better', 'lower_is_better']);
            $table->string('unit')->default('%');
            $table->text('formula_description')->nullable();
            $table->decimal('scale_divider', 15, 2)->default(1);
            $table->boolean('is_annualized')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['pillar', 'is_active']);
        });

        Schema::create('fin_ratio_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('fin_periods')->cascadeOnDelete();
            $table->foreignId('ratio_configuration_id')->constrained('fin_ratio_configurations')->cascadeOnDelete();
            $table->decimal('raw_value', 12, 4);
            $table->decimal('computed_score', 6, 2);
            $table->string('status')->default('achieved'); // achieved, warning, critical
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->unique(['period_id', 'ratio_configuration_id']);
        });

        Schema::create('fin_headcounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('fin_periods')->cascadeOnDelete();
            $table->unsignedInteger('total_headcount');
            $table->unsignedInteger('direct_labor_count');
            $table->decimal('working_hours', 10, 2);
            $table->decimal('overtime_cost', 15, 2)->default(0);
            $table->decimal('total_payroll', 15, 2)->default(0);
            $table->timestamps();

            $table->unique('period_id');
        });

        Schema::create('fin_paid_in_capitals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('fin_periods')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->string('notary_deed_number')->nullable();
            $table->date('effective_date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('period_id');
        });

        Schema::create('fin_npd_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_code');
            $table->string('product_name');
            $table->date('launch_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('product_code');
        });

        Schema::create('fin_production_feeds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('fin_periods')->cascadeOnDelete();
            $table->date('log_date');
            $table->decimal('actual_output_units', 12, 2);
            $table->decimal('target_output_units', 12, 2);
            $table->decimal('reject_rate_pct', 5, 2)->default(0);
            $table->decimal('material_loss_value', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('fin_source_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('fin_periods')->cascadeOnDelete();
            $table->string('rule_code'); // R1, R2, R3, R4, R5, R6, R7, R8, R9
            $table->string('rule_name');
            $table->decimal('gl_value', 15, 2);
            $table->decimal('target_value', 15, 2);
            $table->decimal('variance', 15, 2)->default(0);
            $table->boolean('is_passed')->default(true);
            $table->json('details_json')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->unique(['period_id', 'rule_code']);
        });

        Schema::create('fin_publications', function (Blueprint $table) {
            $table->id();
            $table->string('publication_number')->unique(); // PUB-202611-0001
            $table->foreignId('period_id')->constrained('fin_periods')->cascadeOnDelete();
            $table->string('status')->default('draft'); // simulation, draft, published, superseded
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->json('snapshot_data_json')->nullable();
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['period_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fin_publications');
        Schema::dropIfExists('fin_source_reconciliations');
        Schema::dropIfExists('fin_production_feeds');
        Schema::dropIfExists('fin_npd_products');
        Schema::dropIfExists('fin_paid_in_capitals');
        Schema::dropIfExists('fin_headcounts');
        Schema::dropIfExists('fin_ratio_values');
        Schema::dropIfExists('fin_ratio_configurations');
        Schema::dropIfExists('fin_dynamic_budgets');
        Schema::dropIfExists('fin_production_plans');
        Schema::dropIfExists('fin_revenue_plans');
        Schema::dropIfExists('fin_monthly_budgets');
        Schema::dropIfExists('fin_budget_categories');
    }
};
