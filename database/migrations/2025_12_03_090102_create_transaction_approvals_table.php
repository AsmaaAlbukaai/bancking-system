<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('action', ['approve', 'reject', 'hold', 'review']);
            $table->text('comments')->nullable();
            $table->enum('level', ['teller', 'supervisor', 'manager', 'director'])->default('director');
            $table->integer('approval_order')->default(1);
            $table->boolean('is_required')->default(true);
            $table->timestamp('action_taken_at')->nullable();
            $table->json('conditions')->nullable();
            $table->timestamps();
            
            $table->index(['transaction_id', 'approver_id']);
            $table->index(['level', 'approval_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_approvals');
    }
};