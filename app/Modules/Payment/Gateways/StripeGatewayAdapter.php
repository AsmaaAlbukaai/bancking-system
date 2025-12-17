<?php

namespace App\Modules\Payment\Gateways;

use App\Models\GatewayTransaction;
use App\Models\PaymentGateway;

/**
 * Adapter لبوابة Stripe (محاكاة).
 * يوضح كيف يمكن تطبيق Adapter Pattern مع بوابات دفع حقيقية.
 * 
 * في التطبيق الحقيقي، ستحتاج إلى:
 * - استخدام Stripe SDK
 * - إرسال طلبات HTTP إلى Stripe API
 * - معالجة الاستجابات والأخطاء
 */
class StripeGatewayAdapter implements PaymentGatewayAdapterInterface
{
    /**
     * بدء عملية دفع في Stripe
     */
    public function initiate(PaymentGateway $gateway, float $amount, array $options = []): GatewayTransaction
    {
        // في التطبيق الحقيقي، ستستخدم Stripe SDK:
        // $stripe = new \Stripe\StripeClient($gateway->credentials['secret_key']);
        // $paymentIntent = $stripe->paymentIntents->create([...]);

        // محاكاة: إنشاء GatewayTransaction
        $gatewayTransaction = GatewayTransaction::create([
            'gateway_id'        => $gateway->id,
            'gateway_reference' => 'stripe_' . time() . '_' . rand(1000, 9999),
            'gateway_status'    => 'initiated',
            'gateway_request'   => [
                'amount'   => $amount,
                'currency' => $options['currency'] ?? 'USD',
                'description' => $options['description'] ?? 'Payment via Stripe',
                'payment_method' => $options['payment_method'] ?? 'card',
            ],
            'currency'          => $options['currency'] ?? 'USD',
            'initiated_at'      => now(),
        ]);

        return $gatewayTransaction;
    }

    /**
     * تأكيد الدفع في Stripe
     */
    public function capture(GatewayTransaction $gatewayTransaction): GatewayTransaction
    {
        // في التطبيق الحقيقي:
        // $paymentIntent = $stripe->paymentIntents->retrieve($gatewayTransaction->gateway_reference);
        // $paymentIntent->confirm();

        // محاكاة: تحديث حالة المعاملة
        $gatewayTransaction->update([
            'gateway_status' => 'captured',
            'gateway_response' => [
                'id' => $gatewayTransaction->gateway_reference,
                'status' => 'succeeded',
                'message' => 'Payment captured successfully',
            ],
            'processed_at' => now(),
            'settled_at'   => now(),
        ]);

        return $gatewayTransaction;
    }
}

