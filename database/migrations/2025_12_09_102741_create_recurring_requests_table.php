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
      Schema::create('recurring_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignId('from_account_id')->nullable()->constrained('accounts');
    $table->foreignId('to_account_id')->nullable()->constrained('accounts');
    $table->enum('type', ['deposit','withdrawal','transfer']);
    $table->decimal('amount', 15, 2);
    $table->enum('frequency', ['daily','weekly','monthly']);
    $table->enum('status', ['pending','approved','rejected'])->default('pending');
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_requests');
    }
};
