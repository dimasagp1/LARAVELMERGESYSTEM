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
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 200)->index();
            $table->string('category', 100)->nullable()->index(); // Raw Material, Packaging Material, Machine & Spareparts, General Services
            $table->string('contact_person', 150)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('phone', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('tax_id', 50)->nullable(); // NPWP
            $table->integer('payment_term_days')->default(30);
            $table->boolean('is_avl')->default(false)->index(); // Approved Vendor List
            $table->decimal('avl_rating', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['code', 'is_avl', 'is_active']);
            $table->index(['name', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
