<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->enum('type', ['card', 'bank', 'wallet', 'crypto', 'international']);
            $table->string('provider');
            $table->string('base_url');
            $table->json('credentials')->nullable();
            $table->json('config')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_test_mode')->default(true);
            $table->decimal('transaction_fee', 5, 2)->default(0);
            $table->decimal('percentage_fee', 5, 2)->default(0);
            $table->decimal('min_amount', 10, 2)->default(0);
            $table->decimal('max_amount', 15, 2)->default(100000);
            $table->json('supported_currencies')->nullable();
            $table->json('supported_countries')->nullable();
            $table->integer('timeout_seconds')->default(30);
            $table->integer('retry_attempts')->default(3);
            $table->json('webhook_config')->nullable();
            $table->timestamps();
        });

        Schema::create('gateway_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('gateway_id')->constrained('payment_gateways')->onDelete('cascade');
            $table->string('gateway_reference')->unique();
            $table->string('gateway_status');
            $table->json('gateway_response')->nullable();
            $table->json('gateway_request')->nullable();
            $table->decimal('gateway_fee', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->timestamp('initiated_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();
            
            $table->index(['gateway_id', 'gateway_status']);
            $table->index('gateway_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_transactions');
        Schema::dropIfExists('payment_gateways');
    }
};