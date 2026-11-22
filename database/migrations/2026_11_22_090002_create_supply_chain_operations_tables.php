<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Goods Receipt (Inbound Receiving at Plant/Warehouse)
        Schema::create('scm_goods_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number')->unique(); // GR-YYYYMM-XXXX
            $table->string('po_number')->nullable();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->string('delivery_order_number'); // Surat Jalan No.
            $table->date('receipt_date');
            $table->foreignId('received_by')->constrained('users');
            $table->string('status')->default('quarantine'); // quarantine, inspected, completed, cancelled
            $table->text('notes')->nullable();
            $table->string('attachment_path')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['grn_number', 'status']);
            $table->index(['po_number', 'vendor_id']);
        });

        Schema::create('scm_goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained('scm_goods_receipts')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('item_name');
            $table->decimal('received_qty', 12, 2);
            $table->decimal('passed_qty', 12, 2)->default(0);
            $table->decimal('rejected_qty', 12, 2)->default(0);
            $table->string('uom');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->string('status')->default('quarantine'); // quarantine, passed, rejected, partial
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['goods_receipt_id', 'product_id']);
            $table->index('status');
        });

        // 2. QC Incoming Inspections (4-Parameter Quality Gatekeeper)
        Schema::create('scm_qc_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_number')->unique(); // QC-YYYYMM-XXXX
            $table->foreignId('goods_receipt_item_id')->constrained('scm_goods_receipt_items')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('batch_number')->nullable();
            $table->decimal('sample_qty', 12, 2)->default(0);
            $table->decimal('passed_qty', 12, 2)->default(0);
            $table->decimal('rejected_qty', 12, 2)->default(0);
            $table->foreignId('inspector_id')->constrained('users');
            $table->date('inspection_date');
            // 4 Mandatory CPOTB / cGMP Checkpoints:
            $table->boolean('is_visual_passed')->default(true);
            $table->boolean('is_lab_passed')->default(true);
            $table->boolean('is_quantity_passed')->default(true);
            $table->boolean('is_docs_passed')->default(true);
            $table->string('status')->default('passed'); // pending, passed, rejected, partial
            $table->text('notes')->nullable();
            $table->string('coa_attachment_path')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();

            $table->index(['inspection_number', 'status']);
        });

        // 3. Material Usage / Bukti Serah Terima Bahan (BSTB) to Production
        Schema::create('scm_material_usages', function (Blueprint $table) {
            $table->id();
            $table->string('usage_number')->unique(); // BSTB-YYYYMM-XXXX
            $table->string('work_order_number'); // SPK / Work Order reference
            $table->foreignId('plant_id')->constrained('plants')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->date('usage_date');
            $table->foreignId('issued_by')->constrained('users'); // Warehouse staff
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete(); // Production operator
            $table->string('status')->default('issued'); // draft, issued, cancelled
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['usage_number', 'status']);
            $table->index('work_order_number');
        });

        Schema::create('scm_material_usage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_usage_id')->constrained('scm_material_usages')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('inventory_stock_id')->nullable()->constrained('scm_inventory_stocks')->nullOnDelete();
            $table->string('batch_number')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->string('uom');
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['material_usage_id', 'product_id']);
        });

        // 4. Material Return (Vendor Reject Memo)
        Schema::create('scm_material_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_number')->unique(); // RET-YYYYMM-XXXX
            $table->foreignId('goods_receipt_id')->nullable()->constrained('scm_goods_receipts')->nullOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->date('return_date');
            $table->text('reason');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('approved'); // draft, submitted, approved, completed, cancelled
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['return_number', 'status']);
            $table->index('vendor_id');
        });

        Schema::create('scm_material_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_return_id')->constrained('scm_material_returns')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('batch_number')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->string('uom');
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index(['material_return_id', 'product_id']);
        });

        // 5. 3-Way Matching AP Audit (PO vs GR vs Vendor Invoice)
        Schema::create('scm_three_way_matchings', function (Blueprint $table) {
            $table->id();
            $table->string('matching_number')->unique(); // TWM-YYYYMM-XXXX
            $table->string('po_number');
            $table->foreignId('goods_receipt_id')->constrained('scm_goods_receipts')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('vendor_invoice_number');
            $table->decimal('po_total_qty', 12, 2);
            $table->decimal('po_unit_price', 15, 2);
            $table->decimal('gr_passed_qty', 12, 2);
            $table->decimal('invoice_qty', 12, 2);
            $table->decimal('invoice_amount', 15, 2);
            $table->decimal('discrepancy_qty', 12, 2)->default(0);
            $table->decimal('discrepancy_amount', 15, 2)->default(0);
            $table->string('status')->default('matched'); // matched, discrepant, approved, rejected
            $table->text('notes')->nullable();
            $table->foreignId('audited_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->timestamps();

            $table->index(['matching_number', 'status']);
            $table->index('po_number');
        });

        // 6. Vendor Bills / Accounts Payable
        Schema::create('scm_vendor_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number')->unique(); // BILL-YYYYMM-XXXX
            $table->foreignId('three_way_matching_id')->nullable()->constrained('scm_three_way_matchings')->nullOnDelete();
            $table->foreignId('goods_receipt_id')->nullable()->constrained('scm_goods_receipts')->nullOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->cascadeOnDelete();
            $table->string('invoice_number');
            $table->date('bill_date');
            $table->date('due_date');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->string('status')->default('approved'); // draft, pending_approval, approved, paid, cancelled
            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['bill_number', 'status']);
            $table->index('vendor_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scm_vendor_bills');
        Schema::dropIfExists('scm_three_way_matchings');
        Schema::dropIfExists('scm_material_return_items');
        Schema::dropIfExists('scm_material_returns');
        Schema::dropIfExists('scm_material_usage_items');
        Schema::dropIfExists('scm_material_usages');
        Schema::dropIfExists('scm_qc_inspections');
        Schema::dropIfExists('scm_goods_receipt_items');
        Schema::dropIfExists('scm_goods_receipts');
    }
};
