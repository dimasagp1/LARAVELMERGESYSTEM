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
        Schema::create('boms', function (Blueprint $table) {
            $table->id();
            $table->string('bom_number', 50)->unique();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('version', 20)->default('1.0');
            $table->decimal('batch_size', 16, 4)->default(1.0000);
            $table->foreignId('batch_uom_id')->constrained('uoms')->restrictOnDelete();
            $table->string('status', 30)->default('draft')->index(); // draft, pending_qa, approved, obsolete
            $table->boolean('is_active')->default(true)->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'status', 'is_active']);
            $table->index(['bom_number', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boms');
    }
};
