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
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained('boms')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->decimal('quantity', 16, 6);
            $table->decimal('percentage', 8, 4)->nullable(); // e.g. 50.0000 %
            $table->foreignId('uom_id')->constrained('uoms')->restrictOnDelete();
            $table->decimal('wastage_percentage', 6, 2)->default(0.00);
            $table->string('stage', 50)->nullable()->index(); // mixing, filling, packing
            $table->text('instructions')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['bom_id', 'product_id']);
            $table->index(['bom_id', 'stage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_items');
    }
};
