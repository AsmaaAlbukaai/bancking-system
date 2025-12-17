<?php

namespace App\Modules\Payment;

use App\Models\GatewayTransaction;
use App\Models\PaymentGateway;
use App\Modules\Account\Account;
use App\Modules\Account\AccountOperationsService;
use App\Modules\Payment\Gateways\PaymentGatewayAdapterInterface;
use App\Modules\Transaction\Transaction;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(
        protected PaymentGatewayAdapterInterface $adapter,
        protected AccountOperationsService $ops
    ) {}

    public function depositViaGateway(
        Account $account,
        PaymentGateway $gateway,
        float $amount,
        array $options = []
    ): GatewayTransaction {
        return DB::transaction(function () use ($account, $gateway, $amount, $options) {

            // ✅ 1) إنشاء Transaction داخلي مبدئي
            $transaction = Transaction::create([
                'reference' => 'GATEWAY-' . time() . '-' . rand(100, 999),
                'to_account_id' => $account->id,
                'amount' => $amount,
                'fee' => $gateway->transaction_fee ?? 0,
                'tax' => 0,
                'net_amount' => $amount - ($gateway->transaction_fee ?? 0),
                'type' => 'deposit',
                'status' => 'pending', // ⬅️ مبدئي
                'description' => $options['description'] ?? 'Deposit via payment gateway',
                'metadata' => [
                    'gateway_id' => $gateway->id,
                    'gateway_code' => $gateway->code,
                ],
            ]);

            // ✅ 2) بدء عملية الدفع (نمرر transaction_id)
            $gatewayTxn = $this->adapter->initiate(
                $gateway,
                $amount,
                array_merge($options, [
                    'transaction_id' => $transaction->id,
                ])
            );

            // ✅ 3) تنفيذ التأكيد (capture)
            $gatewayTxn = $this->adapter->capture($gatewayTxn);

            // ✅ 4) تنفيذ الإيداع بالحساب
            $this->ops->deposit($account, $amount);

            // ✅ 5) تحديث Transaction بعد النجاح
            $transaction->update([
                'status' => 'completed',
                'processed_at' => now(),
                'metadata' => array_merge(
                    $transaction->metadata ?? [],
                    [
                        'gateway_transaction_id' => $gatewayTxn->id,
                        'gateway_reference' => $gatewayTxn->gateway_reference,
                    ]
                ),
            ]);

            return $gatewayTxn;
        });
    }
}
