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
        Schema::table('products', function (Blueprint $table) {
            $table->json('custom_attributes')->nullable()->after('specifications_json');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->json('custom_attributes')->nullable()->after('notes');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->json('custom_attributes')->nullable()->after('description');
        });

        Schema::table('boms', function (Blueprint $table) {
            $table->json('custom_attributes')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('custom_attributes');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('custom_attributes');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn('custom_attributes');
        });

        Schema::table('boms', function (Blueprint $table) {
            $table->dropColumn('custom_attributes');
        });
    }
};
