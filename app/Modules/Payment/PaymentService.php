<?php

namespace App\Modules\Payment;

use App\Models\GatewayTransaction;
use App\Models\PaymentGateway;
use App\Modules\Account\Account;
use App\Modules\Account\AccountOperationsService;
use App\Modules\Payment\Gateways\PaymentGatewayAdapterInterface;
use App\Modules\Transaction\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * طبقة خدمة توحّد التعامل مع بوابات الدفع (Adapter) مع منطق الإيداع في الحساب.
 * يستخدم Adapter Pattern لربط بوابات مختلفة مع منطق الإيداع.
 */
class PaymentService
{
    public function __construct(
        protected PaymentGatewayAdapterInterface $adapter,
        protected AccountOperationsService $ops
    ) {}

    /**
     * تنفيذ إيداع عبر بوابة دفع خارجية:
     * 1- بدء عملية الدفع في البوابة (Adapter)
     * 2- عند نجاح العملية، يتم الإيداع في حساب المستخدم
     * 3- إنشاء Transaction داخلي لتسجيل العملية
     */
    public function depositViaGateway(Account $account, PaymentGateway $gateway, float $amount, array $options = []): GatewayTransaction
    {
        return DB::transaction(function () use ($account, $gateway, $amount, $options) {
            // 1) بدء عملية الدفع في البوابة (Adapter Pattern)
            $gatewayTxn = $this->adapter->initiate($gateway, $amount, $options);

            // 2) محاكاة تأكيد الدفع (capture)
            $gatewayTxn = $this->adapter->capture($gatewayTxn);

            // 3) تنفيذ الإيداع الفعلي بالحساب البنكي
            $this->ops->deposit($account, $amount);

            // 4) إنشاء Transaction داخلي لتسجيل العملية
            $transaction = Transaction::create([
                'reference' => 'GATEWAY-' . time() . '-' . rand(100, 999),
                'to_account_id' => $account->id,
                'amount' => $amount,
                'fee' => $gateway->transaction_fee ?? 0,
                'tax' => 0,
                'net_amount' => $amount - ($gateway->transaction_fee ?? 0),
                'type' => 'deposit',
                'status' => 'completed',
                'description' => $options['description'] ?? 'Deposit via payment gateway',
                'metadata' => [
                    'gateway_id' => $gateway->id,
                    'gateway_code' => $gateway->code,
                    'gateway_transaction_id' => $gatewayTxn->id,
                    'gateway_reference' => $gatewayTxn->gateway_reference,
                ],
                'processed_at' => now(),
            ]);

            // 5) ربط GatewayTransaction بالـ Transaction
            $gatewayTxn->update([
                'transaction_id' => $transaction->id,
            ]);

            return $gatewayTxn;
        });
    }
}


