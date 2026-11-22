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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')
                ->nullable()
                ->after('email_verified_at')
                ->constrained('departments')
                ->nullOnDelete();
            $table->string('status', 30)->default('active')->after('password')->index();
            $table->string('signature_path', 255)->nullable()->after('status');
            $table->longText('face_descriptor')->nullable()->after('signature_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'status', 'signature_path', 'face_descriptor']);
        });
    }
};
