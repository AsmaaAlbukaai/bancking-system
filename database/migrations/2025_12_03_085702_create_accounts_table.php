<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('account_number')->unique();
            $table->enum('type', ['savings', 'checking', 'loan', 'investment', 'business']);
            $table->string('account_name')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('minimum_balance', 15, 2)->default(0);
            $table->enum('status', ['active', 'frozen', 'suspended', 'closed', 'dormant'])->default('active');
            $table->foreignId('parent_account_id')->nullable()->constrained('accounts')->onDelete('cascade');
            $table->foreignId('group_id')->nullable()->constrained('account_groups')->onDelete('set null');
            $table->date('opened_at')->default(now());
            $table->date('closed_at')->nullable();
            $table->text('closure_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'type']);
            $table->index('account_number');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }

};    