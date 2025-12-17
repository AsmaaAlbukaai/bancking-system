<?php

namespace App\Modules\Payment\Gateways;

use App\Models\GatewayTransaction;
use App\Models\PaymentGateway;

/**
 * Adapter عام لأي بوابة دفع خارجية.
 * يوفّر واجهة موحدة للتعامل مع البوابات المختلفة.
 */
interface PaymentGatewayAdapterInterface
{
    public function initiate(PaymentGateway $gateway, float $amount, array $options = []): GatewayTransaction;

    public function capture(GatewayTransaction $gatewayTransaction): GatewayTransaction;
}


