<?php

namespace App\Modules\Payment\Gateways;

use App\Models\GatewayTransaction;
use App\Models\PaymentGateway;

/**
 * مثال Adapter لبوابة دفع (وهمية) يوضّح كيفية ربط البوابات الخارجية.
 * يمكن لاحقاً استبداله/توسيعه لتكامل حقيقي مع Stripe/PayPal وغيرهما.
 */
class DummyGatewayAdapter implements PaymentGatewayAdapterInterface
{
    public function initiate(PaymentGateway $gateway, float $amount, array $options = []): GatewayTransaction
    {
        // هنا يفترض أن يتم إرسال طلب HTTP فعلي للبوابة.
        // لأغراض المثال، نخزّن بيانات طلب وهمية.

        return GatewayTransaction::create([
            'gateway_id'        => $gateway->id,
            'gateway_reference' => 'DUMMY-' . time(),
            'gateway_status'    => 'initiated',
            'gateway_request'   => [
                'amount'   => $amount,
                'options'  => $options,
            ],
            'currency'          => $options['currency'] ?? 'USD',
            'initiated_at'      => now(),
        ]);
    }

    public function capture(GatewayTransaction $gatewayTransaction): GatewayTransaction
    {
        // في حالة حقيقية، يتم هنا استعلام البوابة عن حالة العملية أو تنفيذ capture.

        $gatewayTransaction->update([
            'gateway_status' => 'captured',
            'gateway_response' => [
                'message' => 'Dummy capture successful',
            ],
            'processed_at' => now(),
            'settled_at'   => now(),
        ]);

        return $gatewayTransaction;
    }
}


