<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('from_account_id')->nullable()->constrained('accounts');
            $table->foreignId('to_account_id')->nullable()->constrained('accounts');
            $table->decimal('amount', 15, 2);
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->enum('type', ['deposit', 'withdrawal', 'transfer', 'payment', 'interest', 'fee', 'refund']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'reversed'])->default('pending');
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('category')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurring_frequency')->nullable();
            $table->timestamp('next_recurring_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['from_account_id', 'to_account_id']);
            $table->index('reference');
            $table->index('type');
            $table->index('status');
            $table->index('created_at');
            $table->index(['type', 'status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};