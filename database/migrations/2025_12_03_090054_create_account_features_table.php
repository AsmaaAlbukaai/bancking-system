<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_features', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('monthly_fee', 10, 2)->default(0);
            $table->decimal('setup_fee', 10, 2)->default(0);
            $table->enum('fee_type', ['fixed', 'percentage', 'tiered'])->default('fixed');
            $table->json('fee_config')->nullable();
            $table->enum('type', ['service', 'protection', 'premium', 'utility'])->default('service');
            $table->json('requirements')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('account_feature_pivot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->foreignId('feature_id')->constrained('account_features')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->decimal('custom_fee', 10, 2)->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();
            $table->timestamp('next_billing_date')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            
            $table->unique(['account_id', 'feature_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_feature_pivot');
        Schema::dropIfExists('account_features');
    }
};