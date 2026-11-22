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
        Schema::table('hris_employees', function (Blueprint $table) {
            if (! Schema::hasColumn('hris_employees', 'shift_id')) {
                $table->foreignId('shift_id')->nullable()->after('grade_id')->constrained('hris_shifts')->nullOnDelete();
            }
            if (! Schema::hasColumn('hris_employees', 'office_location_id')) {
                $table->foreignId('office_location_id')->nullable()->after('shift_id')->constrained('hris_office_locations')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hris_employees', function (Blueprint $table) {
            if (Schema::hasColumn('hris_employees', 'office_location_id')) {
                $table->dropForeign(['office_location_id']);
                $table->dropColumn('office_location_id');
            }
            if (Schema::hasColumn('hris_employees', 'shift_id')) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            }
        });
    }
};
