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
        Schema::create('erp_feature_records', function (Blueprint $table) {
            $table->id();
            $table->string('module_slug', 64)->index();
            $table->string('feature_route', 191)->index();
            $table->string('record_code', 64)->index();
            $table->string('title');
            $table->string('category', 100)->default('Operasional')->index();
            $table->string('pic_name', 150)->nullable();
            $table->date('entry_date')->nullable();
            $table->string('status', 50)->default('Draft')->index();
            $table->decimal('amount', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->json('custom_attributes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['feature_route', 'status']);
            $table->index(['module_slug', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_feature_records');
    }
};
