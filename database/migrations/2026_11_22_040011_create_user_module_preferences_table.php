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
        Schema::create('user_module_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('module_id')->constrained('erp_modules')->cascadeOnDelete();
            $table->boolean('is_pinned')->default(false)->index();
            $table->integer('custom_order')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'module_id']);
            $table->index(['user_id', 'is_pinned', 'custom_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_module_preferences');
    }
};
