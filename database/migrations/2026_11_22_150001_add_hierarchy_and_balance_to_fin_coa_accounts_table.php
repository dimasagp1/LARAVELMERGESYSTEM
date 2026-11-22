<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fin_coa_accounts', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('fin_coa_accounts')->nullOnDelete();
            $table->boolean('is_header')->default(false)->after('normal_balance');
            $table->decimal('opening_balance', 15, 2)->default(0)->after('is_header');
            $table->text('description')->nullable()->after('opening_balance');
        });
    }

    public function down(): void
    {
        Schema::table('fin_coa_accounts', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'is_header', 'opening_balance', 'description']);
        });
    }
};
