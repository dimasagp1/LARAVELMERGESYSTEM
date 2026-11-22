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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 100)->unique();
            $table->string('name', 200)->index();
            $table->string('type', 30)->index(); // fg, rm, pm, wip, atk, sparepart
            $table->string('category', 100)->nullable()->index();
            $table->foreignId('uom_id')->constrained('uoms')->restrictOnDelete();
            $table->foreignId('secondary_uom_id')->nullable()->constrained('uoms')->nullOnDelete();
            $table->decimal('conversion_rate', 16, 6)->default(1.000000);
            $table->decimal('min_stock', 16, 4)->default(0.0000);
            $table->decimal('safety_stock', 16, 4)->default(0.0000);
            $table->decimal('reorder_point', 16, 4)->default(0.0000);
            $table->decimal('standard_cost', 16, 4)->default(0.0000);
            $table->decimal('selling_price', 16, 4)->default(0.0000);
            $table->text('description')->nullable();
            $table->json('specifications_json')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'category', 'is_active']);
            $table->index(['sku', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
