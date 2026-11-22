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
        Schema::create('approval_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->foreignId('step_id')->nullable()->constrained('approval_steps')->nullOnDelete();
            $table->string('approvable_type', 150);
            $table->unsignedBigInteger('approvable_id');
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action', 50)->index(); // submitted, approved, rejected, revised, delegated
            $table->text('comments')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('signature_snapshot')->nullable(); // Dual-control / 21 CFR Part 11 signature snapshot
            $table->timestamp('acted_at')->useCurrent()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['approvable_type', 'approvable_id', 'action']);
            $table->index(['workflow_id', 'step_id']);
            $table->index(['user_id', 'acted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_logs');
    }
};
