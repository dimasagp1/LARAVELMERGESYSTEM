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
        Schema::create('approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->integer('step_order')->default(1);
            $table->string('step_name', 150);
            $table->string('approver_type', 50)->default('role'); // role, user, department_head, creator_manager
            $table->foreignId('approver_role_id')->nullable()->constrained('roles')->nullOnDelete();
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('sla_hours')->nullable();
            $table->boolean('requires_digital_signature')->default(false);
            $table->boolean('can_reject')->default(true);
            $table->boolean('can_revise')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['workflow_id', 'step_order']);
            $table->index(['approver_type', 'approver_role_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_steps');
    }
};
