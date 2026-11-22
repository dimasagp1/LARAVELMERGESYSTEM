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
        Schema::create('erp_modules', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('icon', 100)->default('Folder');
            $table->string('color_theme', 50)->default('emerald'); // emerald, blue, indigo, violet, amber, rose, cyan, slate
            $table->string('route_prefix', 100)->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_core')->default(false)->index();
            $table->boolean('is_custom')->default(false)->index();
            $table->integer('sort_order')->default(0)->index();
            $table->json('schema_config_json')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
            $table->index(['slug', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('erp_modules');
    }
};
