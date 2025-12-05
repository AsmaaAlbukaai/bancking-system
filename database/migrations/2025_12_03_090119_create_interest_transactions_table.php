<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interest_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->decimal('principal', 15, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->enum('calculation_method', ['simple', 'compound', 'flat', 'effective']);
            $table->string('period');
            $table->integer('days')->default(30);
            $table->decimal('interest_amount', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('net_interest', 15, 2);
            $table->date('calculation_date');
            $table->date('applicable_from');
            $table->date('applicable_to');
            $table->boolean('is_applied')->default(false);
            $table->timestamp('applied_at')->nullable();
            $table->foreignId('applied_by')->nullable()->constrained('users');
            $table->json('calculation_details')->nullable();
            $table->timestamps();
            
            $table->index(['account_id', 'calculation_date']);
            $table->index('is_applied');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interest_calculations');
    }
};