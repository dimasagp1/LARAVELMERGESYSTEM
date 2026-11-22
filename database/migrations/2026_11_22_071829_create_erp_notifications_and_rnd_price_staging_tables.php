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
        Schema::create('erp_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('module_slug');
            $table->string('type')->default('general');
            $table->string('title');
            $table->text('message');
            $table->string('action_url')->nullable();
            $table->string('urgency')->default('normal'); // low, normal, high, critical
            $table->json('data_json')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['module_slug', 'is_read']);
            $table->index('created_at');
        });

        Schema::create('rnd_price_stagings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->string('source_type')->default('po'); // po, quotation, manual
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('po_number')->nullable();
            $table->decimal('old_price', 15, 2)->default(0);
            $table->decimal('new_price', 15, 2);
            $table->decimal('price_difference', 15, 2)->default(0);
            $table->decimal('percentage_change', 8, 2)->default(0);
            $table->string('status')->default('staged'); // staged, applied, rejected
            $table->date('effective_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'status']);
            $table->index('po_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rnd_price_stagings');
        Schema::dropIfExists('erp_notifications');
    }
};
