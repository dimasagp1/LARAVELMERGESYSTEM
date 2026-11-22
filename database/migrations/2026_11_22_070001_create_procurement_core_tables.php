<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('pr_number')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->date('request_date');
            $table->string('purpose'); // Biaya Operasional, Bahan Baku, Pemeliharaan Mesin, etc.
            $table->enum('pr_type', ['operational', 'non_operational'])->default('operational');
            $table->string('status')->default('draft'); // draft, pending, ordered, delivered, completed, cancelled
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->boolean('bypass_budget_check')->default(false);
            $table->text('bypass_budget_reason')->nullable();
            $table->unsignedInteger('revision_count')->default(0);
            $table->foreignId('parent_pr_id')->nullable()->constrained('purchase_requests')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['pr_number', 'status']);
            $table->index(['department_id', 'pr_type']);
        });

        Schema::create('pr_bons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->nullable()->constrained('purchase_requests')->nullOnDelete();
            $table->string('bon_number')->unique();
            $table->string('vendor_name');
            $table->date('bon_date');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('waiting_gm'); // waiting_gm, revision, approved_gm, partial_approved, rejected_gm
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('revision_note')->nullable();
            $table->json('revised_items')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['bon_number', 'status']);
        });

        Schema::create('pr_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();
            $table->foreignId('pr_bon_id')->nullable()->constrained('pr_bons')->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('item_name');
            $table->string('item_code')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->decimal('ready_quantity', 12, 2)->default(0);
            $table->string('uom');
            $table->decimal('estimated_price', 15, 2)->default(0);
            $table->decimal('actual_price', 15, 2)->default(0);
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('vendor_name')->nullable();
            $table->string('po_number')->nullable();
            // 8-step Granular Workflow status:
            // draft, pending_estimate, pending, approved_om, waiting_quotation_holding,
            // waiting_actual_price, waiting_gm, waiting_bon, waiting_gm_bon,
            // ready_to_po, ordered, delivered, completed, rejected_om, rejected_gm, rejected_proc
            $table->string('status')->default('pending_estimate');
            $table->boolean('rekap_po_odoo')->default(false);
            $table->boolean('is_incoming')->default(true);
            $table->boolean('need_holding_quotation')->default(false);
            $table->boolean('need_holding_delivery')->default(false);
            $table->boolean('is_bon')->default(false);
            $table->decimal('bon_unit_price', 15, 2)->nullable();
            $table->boolean('po_manual_odoo')->default(false);
            $table->text('po_manual_reason')->nullable();
            $table->foreignId('po_manual_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('po_manual_at')->nullable();
            $table->text('validation_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->string('attachment_path')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['purchase_request_id', 'status']);
            $table->index('po_number');
            $table->index('is_bon');
        });

        Schema::create('pr_item_delivery_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_item_id')->constrained('pr_items')->cascadeOnDelete();
            $table->date('plan_date');
            $table->decimal('plan_quantity', 12, 2);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_rescheduled')->default(false);
            $table->date('original_plan_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('pr_item_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pr_item_id')->constrained('pr_items')->cascadeOnDelete();
            $table->foreignId('retur_for_delivery_id')->nullable()->constrained('pr_item_deliveries')->nullOnDelete();
            $table->date('delivery_date');
            $table->decimal('delivered_quantity', 12, 2);
            $table->string('delivery_order_number'); // No Surat Jalan
            $table->string('attachment_path')->nullable();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_rejected')->default(false);
            $table->text('reject_reason')->nullable();
            $table->decimal('rejected_quantity', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('pr_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->cascadeOnDelete();
            $table->foreignId('pr_item_id')->nullable()->constrained('pr_items')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('approval_type'); // om, fatm, gm, proc, holding
            $table->string('status'); // approved, rejected, note
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['purchase_request_id', 'approval_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pr_approvals');
        Schema::dropIfExists('pr_item_deliveries');
        Schema::dropIfExists('pr_item_delivery_plans');
        Schema::dropIfExists('pr_items');
        Schema::dropIfExists('pr_bons');
        Schema::dropIfExists('purchase_requests');
    }
};
