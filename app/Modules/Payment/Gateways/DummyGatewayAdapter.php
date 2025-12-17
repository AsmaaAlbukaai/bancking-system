<?php

namespace App\Modules\Payment\Gateways;

use App\Models\GatewayTransaction;
use App\Models\PaymentGateway;

class DummyGatewayAdapter implements PaymentGatewayAdapterInterface
{
    public function initiate(
        PaymentGateway $gateway,
        float $amount,
        array $options = []
    ): GatewayTransaction {
        return GatewayTransaction::create([
            // ✅ الحل الأساسي
            'transaction_id'   => $options['transaction_id'],

            'gateway_id'        => $gateway->id,
            'gateway_reference' => 'DUMMY-' . time(),
            'gateway_status'    => 'initiated',
            'gateway_request'   => [
                'amount'  => $amount,
                'options' => $options,
            ],
            'currency'          => $options['currency'] ?? 'USD',
            'initiated_at'      => now(),
        ]);
    }

    public function capture(GatewayTransaction $gatewayTransaction): GatewayTransaction
    {
        $gatewayTransaction->update([
            'gateway_status'   => 'captured',
            'gateway_response' => [
                'message' => 'Dummy capture successful',
            ],
            'processed_at' => now(),
            'settled_at'   => now(),
        ]);

        return $gatewayTransaction;
    }
}
