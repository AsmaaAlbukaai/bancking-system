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
    Schema::create('account_status_change_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('account_id')->constrained('accounts');
    $table->string('requested_status');
    $table->string('current_status');
    $table->string('approval_level')->default('teller'); // employee, manager
    $table->string('status')->default('pending'); // approved, rejected, pending
    $table->foreignId('approved_by')->nullable()->constrained('users');
    $table->foreignId('requested_by')->nullable()->constrained('users');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
