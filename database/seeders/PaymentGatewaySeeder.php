<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * إنشاء بوابات دفع تجريبية للاستخدام في الـ API
     */
    public function run(): void
    {
        // تعطيل فحص القيود مؤقتًا (لمنع مشاكل العلاقات)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PaymentGateway::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Dummy Gateway
        PaymentGateway::create([
            'name' => 'Dummy Gateway',
            'code' => 'dummy',
            'type' => 'international', // ✔️ قيمة صحيحة من enum
            'provider' => 'Internal',
            'base_url' => 'https://dummy-gateway.test',
            'credentials' => [
                'api_key' => 'dummy-key',
            ],
            'config' => [],
            'is_active' => true,
            'is_test_mode' => true,
            'transaction_fee' => 1.00,
            'percentage_fee' => 0.00,
            'min_amount' => 1.00,
            'max_amount' => 100000.00,
            'supported_currencies' => ['USD', 'EUR'],
            'supported_countries' => ['US', 'EU'],
            'timeout_seconds' => 30,
            'retry_attempts' => 3,
            'webhook_config' => [],
        ]);

        // Stripe Sandbox
        PaymentGateway::create([
            'name' => 'Stripe (Sandbox)',
            'code' => 'stripe',
            'type' => 'card', // ✔️ قيمة صحيحة
            'provider' => 'Stripe',
            'base_url' => 'https://api.stripe.com',
            'credentials' => [
                'public_key' => 'pk_test_xxx',
                'secret_key' => 'sk_test_xxx',
            ],
            'config' => [
                'webhook_secret' => 'whsec_xxx',
            ],
            'is_active' => true,
            'is_test_mode' => true,
            'transaction_fee' => 0.50,
            'percentage_fee' => 2.90,
            'min_amount' => 1.00,
            'max_amount' => 50000.00,
            'supported_currencies' => ['USD', 'EUR'],
            'supported_countries' => ['US', 'GB', 'DE'],
            'timeout_seconds' => 60,
            'retry_attempts' => 3,
            'webhook_config' => [],
        ]);
    }
}
