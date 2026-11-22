<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sales History Data for PPIC & Forecasting
        Schema::create('scm_sales_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->decimal('quantity_sold', 12, 2);
            $table->decimal('revenue_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'year', 'month']);
            $table->index(['year', 'month']);
        });

        // 2. Sales Forecasts (Annual & Rolling)
        Schema::create('scm_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('type', ['annual', 'rolling'])->default('annual');
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month')->nullable(); // null for annual target
            $table->decimal('forecast_quantity', 12, 2);
            $table->decimal('forecast_value', 15, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'type', 'period_year']);
        });

        // 3. Master Production Schedule (MPS)
        Schema::create('scm_production_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('schedule_number')->unique(); // MPS-YYYYMM-XXXX
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('bom_id')->nullable()->constrained('boms')->nullOnDelete();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('planned_quantity', 12, 2);
            $table->decimal('produced_quantity', 12, 2)->default(0);
            $table->string('status')->default('confirmed'); // draft, confirmed, in_progress, completed, cancelled
            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['schedule_number', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // 4. Material Requirements Planning (MRP) Simulation & Runs
        Schema::create('scm_mrp_runs', function (Blueprint $table) {
            $table->id();
            $table->string('run_number')->unique(); // MRP-YYYYMM-XXXX
            $table->foreignId('schedule_id')->nullable()->constrained('scm_production_schedules')->nullOnDelete();
            $table->string('period_name'); // e.g. November 2026
            $table->json('parameters_json')->nullable();
            $table->string('status')->default('simulated'); // simulated, applied, archived
            $table->foreignId('executed_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['run_number', 'status']);
        });

        Schema::create('scm_mrp_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mrp_run_id')->constrained('scm_mrp_runs')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('bom_id')->nullable()->constrained('boms')->nullOnDelete();
            $table->decimal('gross_requirement', 12, 2);
            $table->decimal('on_hand_stock', 12, 2)->default(0);
            $table->decimal('allocated_stock', 12, 2)->default(0);
            $table->decimal('outstanding_po_qty', 12, 2)->default(0);
            $table->decimal('safety_stock', 12, 2)->default(0);
            $table->decimal('net_requirement', 12, 2)->default(0);
            $table->date('suggested_order_date')->nullable();
            $table->date('suggested_due_date')->nullable();
            $table->string('status')->default('pending'); // pending, pr_created, ignored
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->timestamps();

            $table->index(['mrp_run_id', 'product_id']);
        });

        // 5. Multi-Warehouse / Multi-Location Stocks
        Schema::create('scm_inventory_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->string('batch_number')->nullable();
            $table->string('lot_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity_on_hand', 12, 2)->default(0);
            $table->decimal('quantity_reserved', 12, 2)->default(0);
            $table->decimal('quantity_available', 12, 2)->default(0);
            $table->string('status')->default('available'); // quarantine, available, reserved, rejected, expired
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'location_id', 'status']);
            $table->index(['batch_number', 'expiry_date']);
        });

        // 6. Stock Movements Audit Ledger
        Schema::create('scm_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->string('movement_number')->unique(); // MOV-YYYYMM-XXXX
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('from_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('reference_type'); // goods_receipt, material_usage, material_return, adjustment, transfer
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->string('batch_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index(['product_id', 'reference_type']);
            $table->index('movement_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scm_stock_movements');
        Schema::dropIfExists('scm_inventory_stocks');
        Schema::dropIfExists('scm_mrp_items');
        Schema::dropIfExists('scm_mrp_runs');
        Schema::dropIfExists('scm_production_schedules');
        Schema::dropIfExists('scm_forecasts');
        Schema::dropIfExists('scm_sales_data');
    }
};
