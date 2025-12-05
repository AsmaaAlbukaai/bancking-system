<?php

namespace App\Modules\Transaction;

use App\Modules\Account\Account;
use App\Modules\Notification\NotificationDispatcher;
use App\Modules\Transaction\Handlers\BaseApprovalHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    protected BaseApprovalHandler $approvalChain;
    protected NotificationDispatcher $notifier;

    public function __construct(BaseApprovalHandler $approvalChain, NotificationDispatcher $notifier)
    {
        $this->approvalChain = $approvalChain;
        $this->notifier = $notifier;
    }

    /**
     * تنفيذ تحويل بين حسابين مع اعتماد سلسلة الموافقات
     */
    public function transfer(Account $from, Account $to, float $amount, array $meta = []): Transaction
    {
        return DB::transaction(function () use ($from, $to, $amount, $meta) {
            // تحقق أساسي
            if (!$from->canWithdraw($amount)) {
                throw new \Exception("Insufficient funds or account state prohibits withdrawal.");
            }

            // انشئ سجل المعاملة
            $txn = Transaction::create([
                'reference' => 'TRX-' . time() . '-' . rand(100,999),
                'from_account_id' => $from->id,
                'to_account_id' => $to->id,
                'amount' => $amount,
                'fee' => 0,
                'net_amount' => $amount,
                'type' => 'transfer',
                'status' => 'pending',
                'metadata' => $meta,
                'processed_at' => null
            ]);

            // مرّر المعاملة عبر سلسلة الموافقات
            $approved = $this->approvalChain->handle($txn);

            if ($approved) {
                // إن تمت الموافقة (مثلاً Auto) قم بتطبيق التغيرات على الحسابات
                $from->withdraw($amount);
                $to->deposit($amount);

                $txn->processed_at = now();
                $txn->status = 'completed';
                $txn->save();

                // اشعار الأطراف
                $this->notifier->dispatch($from->user, 'transaction', "تم تحويل {$amount} من حسابك", [
                    'transaction_id' => $txn->id
                ]);
                $this->notifier->dispatch($to->user, 'transaction', "تم استلام {$amount} في حسابك", [
                    'transaction_id' => $txn->id
                ]);
            } else {
                // حالة انتظار الموافقة تم ضبطها داخل handler (مثلاً awaiting_approval)
                Log::info("Transaction {$txn->id} awaiting manual approval.");
            }

            return $txn;
        });
    }
}
