<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. System Settings Table
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 50)->default('general')->index(); // general, branding, numbering, theme, api
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type', 20)->default('string'); // string, boolean, integer, json, text
            $table->string('description')->nullable();
            $table->boolean('is_public')->default(false)->index();
            $table->timestamps();
        });

        // 2. User Module & Menu Access Permissions (Per-User Checklist)
        Schema::create('user_access_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('module_slug', 50)->index();
            $table->string('menu_route')->nullable()->index(); // null = module-level access, string = menu-level route
            $table->boolean('can_view')->default(true);
            $table->boolean('can_create')->default(true);
            $table->boolean('can_edit')->default(true);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_approve')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'module_slug', 'menu_route'], 'user_mod_menu_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_access_permissions');
        Schema::dropIfExists('system_settings');
    }
};
