<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fin_fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Tahun Anggaran 2026
            $table->unsignedSmallInteger('year')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_closed')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('fin_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained('fin_fiscal_years')->cascadeOnDelete();
            $table->unsignedTinyInteger('period_num'); // 1..12
            $table->string('period_name'); // e.g. 2026-11, November 2026
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('open'); // open, review, published, closed
            $table->timestamps();

            $table->unique(['year', 'month']);
            $table->index(['year', 'status']);
        });

        Schema::create('fin_fsli', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // REV_SALES, COGS, OPEX_SALES, OPEX_GA, etc.
            $table->string('name');
            $table->enum('statement_type', ['income_statement', 'balance_sheet', 'cash_flow', 'equity']);
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->foreignId('parent_id')->nullable()->constrained('fin_fsli')->nullOnDelete();
            $table->timestamps();

            $table->index(['statement_type', 'sort_order']);
        });

        Schema::create('fin_coa_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fsli_id')->constrained('fin_fsli')->cascadeOnDelete();
            $table->string('account_code')->unique(); // 41000010, 11000010
            $table->string('account_name');
            $table->string('account_type'); // asset, liability, equity, income, expense
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['account_code', 'is_active']);
        });

        Schema::create('fin_coa_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('odoo_account_code');
            $table->string('odoo_account_name');
            $table->foreignId('fsli_id')->constrained('fin_fsli')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['odoo_account_code', 'is_active']);
        });

        Schema::create('fin_gl_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('fin_periods')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->string('move_id')->nullable(); // Odoo journal move ref
            $table->string('doc_ref')->nullable(); // INV/2026/001
            $table->string('journal_name')->nullable();
            $table->string('account_code');
            $table->string('account_name');
            $table->foreignId('fsli_id')->constrained('fin_fsli')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('partner_name')->nullable();
            $table->text('description')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('signed_amount', 15, 2)->default(0);
            $table->boolean('is_synced')->default(true);
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['transaction_date', 'account_code']);
            $table->index(['period_id', 'fsli_id']);
        });

        Schema::create('fin_adj_journals', function (Blueprint $table) {
            $table->id();
            $table->string('journal_number')->unique();
            $table->foreignId('fiscal_year_id')->constrained('fin_fiscal_years')->cascadeOnDelete();
            $table->foreignId('period_id')->constrained('fin_periods')->cascadeOnDelete();
            $table->date('journal_date');
            $table->text('description');
            $table->string('category')->default('accrual'); // accrual, depreciation, amortization, correction, bad_debt
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->string('status')->default('draft'); // draft, submitted, approved, rejected, reversed
            $table->string('attachment_path')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['journal_number', 'status']);
            $table->index(['period_id', 'category']);
        });

        Schema::create('fin_adj_journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adj_journal_id')->constrained('fin_adj_journals')->cascadeOnDelete();
            $table->foreignId('coa_account_id')->nullable()->constrained('fin_coa_accounts')->nullOnDelete();
            $table->string('account_code');
            $table->string('account_name');
            $table->foreignId('fsli_id')->constrained('fin_fsli')->cascadeOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['adj_journal_id', 'account_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fin_adj_journal_lines');
        Schema::dropIfExists('fin_adj_journals');
        Schema::dropIfExists('fin_gl_entries');
        Schema::dropIfExists('fin_coa_mappings');
        Schema::dropIfExists('fin_coa_accounts');
        Schema::dropIfExists('fin_fsli');
        Schema::dropIfExists('fin_periods');
        Schema::dropIfExists('fin_fiscal_years');
    }
};
