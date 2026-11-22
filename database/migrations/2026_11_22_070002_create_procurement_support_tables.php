<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_standard_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('item_code');
            $table->string('item_name');
            $table->enum('category_type', ['material', 'supporting'])->default('material');
            $table->string('uom');
            $table->decimal('standard_price', 15, 2);
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('vendor_name')->nullable();
            $table->string('vendor_code')->nullable();
            $table->string('po_number')->nullable();
            $table->dateTime('po_date')->nullable();
            $table->unsignedSmallInteger('effective_year')->default(2026);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['item_code', 'effective_year']);
            $table->index(['category_type', 'is_active']);
        });

        Schema::create('production_loss_time_logs', function (Blueprint $table) {
            $table->id();
            $table->date('log_date');
            $table->string('machine_name');
            $table->decimal('downtime_hours', 8, 2);
            $table->string('material_name');
            $table->decimal('output_per_hour', 10, 2);
            $table->decimal('product_unit_value', 15, 2);
            $table->decimal('loss_value', 15, 2);
            $table->decimal('target_revenue', 15, 2)->default(5000000000);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('log_date');
        });

        Schema::create('department_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->unsignedSmallInteger('fiscal_year')->default(2026);
            $table->unsignedTinyInteger('month')->default(11);
            $table->string('purpose');
            $table->decimal('initial_budget', 15, 2)->default(0);
            $table->decimal('realized_amount', 15, 2)->default(0);
            $table->decimal('booked_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['department_id', 'fiscal_year', 'month', 'purpose'], 'dept_budget_unique');
        });

        Schema::create('expense_stagings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();
            $table->foreignId('pr_item_id')->nullable()->constrained('pr_items')->nullOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->string('purpose');
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('committed'); // committed, realized, cancelled
            $table->timestamp('staged_at')->useCurrent();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['purchase_request_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_stagings');
        Schema::dropIfExists('department_budgets');
        Schema::dropIfExists('production_loss_time_logs');
        Schema::dropIfExists('item_standard_prices');
    }
};
