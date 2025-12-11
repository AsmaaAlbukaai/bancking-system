<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // صاحب الطلب

            $table->enum('type', ['inquiry', 'complaint']); // استفسار - شكوى
            $table->string('subject');
            $table->text('message');

            $table->enum('status', ['open', 'pending', 'answered', 'closed'])->default('open');

            // لتحديد من يجب أن يتعامل مع التذكرة
            $table->enum('assigned_to_role', ['teller', 'manager'])->nullable();

            // آخر من ردّ
            $table->foreignId('last_reply_by')->nullable()->constrained('users');

            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
