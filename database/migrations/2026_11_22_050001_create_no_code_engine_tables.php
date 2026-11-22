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
        Schema::create('custom_entities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('erp_modules')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug', 100);
            $table->string('table_type', 50)->default('jsonb'); // jsonb or physical
            $table->string('icon', 100)->default('Folder');
            $table->text('description')->nullable();
            $table->boolean('has_approval')->default(false);
            $table->boolean('has_audit_log')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['module_id', 'slug']);
            $table->index(['module_id', 'name']);
        });

        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('custom_entities')->cascadeOnDelete();
            $table->string('field_key', 100);
            $table->string('label', 150);
            $table->string('field_type', 50)->index(); // text, number, currency, date, select, relation, file, signature
            $table->json('options_json')->nullable(); // dropdown items or relation target
            $table->string('validation_rules', 255)->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('show_in_table')->default(true);
            $table->integer('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['entity_id', 'field_key']);
            $table->index(['entity_id', 'sort_order']);
        });

        Schema::create('custom_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('custom_entities')->cascadeOnDelete();
            $table->string('record_number', 100)->index();
            $table->json('data_json');
            $table->string('status', 50)->default('draft')->index(); // draft, submitted, approved, rejected
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['entity_id', 'status']);
            $table->index(['entity_id', 'record_number']);
        });

        Schema::create('custom_dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('erp_modules')->cascadeOnDelete();
            $table->string('widget_type', 50)->index(); // kpi, chart, table, inbox
            $table->string('title', 150);
            $table->json('data_source_config_json')->nullable();
            $table->integer('grid_span')->default(1);
            $table->integer('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['module_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_dashboard_widgets');
        Schema::dropIfExists('custom_records');
        Schema::dropIfExists('custom_fields');
        Schema::dropIfExists('custom_entities');
    }
};
