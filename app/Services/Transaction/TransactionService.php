<?php

namespace App\Services\Transaction;

use App\Models\Transaction;
use App\Models\Account;
use App\Services\Transaction\Handlers\BaseApprovalHandler;
use App\Services\Notification\NotificationDispatcher;
use App\Services\Account\AccountOperationsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TransactionService
{
    protected BaseApprovalHandler $approvalChain;
    protected NotificationDispatcher $notifier;
    protected AccountOperationsService $ops;

    public function __construct(
        BaseApprovalHandler $approvalChain,
        NotificationDispatcher $notifier,
        AccountOperationsService $ops
    ) {
        $this->approvalChain = $approvalChain;
        $this->notifier = $notifier;
        $this->ops = $ops;
    }

    public function transfer(Account $from, Account $to, float $amount, array $meta = []): Transaction
    {
        if ($from->id === $to->id) {
            throw new Exception("Cannot transfer to the same account.");
        }

        if (!$this->ops->canWithdraw($from, $amount)) {
            throw new Exception("Insufficient funds or withdrawal not allowed.");
        }

        return DB::transaction(function () use ($from, $to, $amount, $meta) {

            // 1️⃣ إنشاء سجل المعاملة أولاً
            $txn = Transaction::create([
                'reference'        => 'TRX-' . time() . '-' . rand(100, 999),
                'from_account_id'  => $from->id,
                'to_account_id'    => $to->id,
                'amount'           => $amount,
                'fee'              => 0,
                'net_amount'       => $amount,
                'type'             => 'transfer',
                'status'           => 'pending',
                'metadata'         => $meta,
                'processed_at'     => null,
            ]);

            // 2️⃣ تمرير *سجل المعاملة* للـ approval chain
            $approved = $this->approvalChain->handle($txn);

            if (!$approved) {
                Log::info("Transaction {$txn->id} awaiting manual approval.");
                return $txn;
            }

            // 3️⃣ تنفيذ العملية بعد الموافقة
            $this->ops->withdraw($from, $amount);
            $this->ops->deposit($to, $amount);

            $txn->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            // 4️⃣ إرسال الإشعارات
            $this->notifier->dispatch(
                $from->user,
                'transaction',
                "تم خصم {$amount} من حسابك.",
                ['transaction_id' => $txn->id]
            );

            $this->notifier->dispatch(
                $to->user,
                'transaction',
                "تم إضافة مبلغ {$amount} إلى حسابك.",
                ['transaction_id' => $txn->id]
            );

            return $txn;
        });
    }
}
