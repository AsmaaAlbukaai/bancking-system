<?php

namespace App\Modules\Transaction;

use App\Modules\Account\Account;
use App\Modules\Account\AccountOperationsService;
use App\Modules\Notification\NotificationDispatcher;
use App\Modules\Transaction\Handlers\BaseApprovalHandler;
use Illuminate\Support\Facades\DB;
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

    /**
     * 🔵 تحويل بين حسابين
     */
    public function transfer(Account $from, Account $to, float $amount, array $meta = []): Transaction
    {
        if ($from->id === $to->id) {
            throw new Exception("Cannot transfer to the same account.");
        }

        if (!$this->ops->canWithdraw($from, $amount)) {
            throw new Exception("Insufficient funds or withdrawal not allowed.");
        }

        return DB::transaction(function () use ($from, $to, $amount, $meta) {

            $txn = Transaction::create([
                'reference'        => 'TRX-' . time() . '-' . rand(100,999),
                'from_account_id'  => $from->id,
                'to_account_id'    => $to->id,
                'amount'           => $amount,
                'fee'              => 0,
                'tax'              => 0,
                'net_amount'       => $amount,
                'type'             => 'transfer',
                'status'           => 'pending',
                'metadata'         => $meta,
            ]);

            $approved = $this->approvalChain->handle($txn);

            if (!$approved) {
                return $txn;
            }

            // تنفيذ العملية
            $this->ops->withdraw($from, $amount);
            $this->ops->deposit($to, $amount);

            $txn->update([
                'status' => 'completed',
                'processed_at' => now(),
            ]);

            return $txn;
        });
    }

    /**
     * 🟢 عملية سحب أو إيداع للزبون - بانتظار موافقة Teller
     */
    public function customerTransaction(Account $acc, float $amount, string $type, array $meta = []): Transaction
{
    if (!in_array($type, ['deposit', 'withdrawal'])) {
        throw new Exception("Invalid transaction type.");
    }

    if ($type === 'withdrawal' && !$this->ops->canWithdraw($acc, $amount)) {
        throw new Exception("Insufficient funds.");
    }

    return DB::transaction(function () use ($acc, $amount, $type, $meta) {

        $txn = Transaction::create([
            'reference'        => 'CUST-' . time() . '-' . rand(100,999),
            'from_account_id'  => $type === 'withdrawal' ? $acc->id : null,
            'to_account_id'    => $type === 'deposit' ? $acc->id : null,
            'amount'           => $amount,
            'fee'              => 0,
            'tax'              => 0,
            'net_amount'       => $amount,
            'type'             => $type,
            'status'           => 'pending',
            'metadata'         => array_merge($meta, [
                'is_customer_transaction' => true
            ]),
        ]);

        // 🔹 نفذ سلسلة الموافقات
        $approved = $this->approvalChain->handle($txn);

        // 🔹 إذا تمت الموافقة تلقائيًا → نفذ العملية المالية
        if ($approved) {

            if ($type === 'withdrawal') {
                $this->ops->withdraw($acc, $amount);
            }

            if ($type === 'deposit') {
                $this->ops->deposit($acc, $amount);
            }

            $txn->update([
                'status' => 'completed',
                'processed_at' => now()
            ]);
        }

        return $txn;
    });
}


    /**
     * ✔ موافقة الموظف teller على المعاملة
     */
    public function approveTransaction(Transaction $txn, $user): Transaction
    {
        if ($txn->status !== 'pending' && $txn->status !== 'processing') {
            throw new Exception("Transaction is not awaiting approval.");
        }

        // تنفيذ العملية بعد الموافقة
        if ($txn->type === 'withdrawal') {
            $this->ops->withdraw($txn->fromAccount, $txn->amount);
        }

        if ($txn->type === 'deposit') {
            $this->ops->deposit($txn->toAccount, $txn->amount);
        }

        $txn->update([
            'status' => 'completed',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'processed_at' => now(),
        ]);

        return $txn;
    }

    /**
     * ❌ رفض المعاملة
     */
    public function rejectTransaction(Transaction $txn, $user): Transaction
    {
        $txn->update([
            'status' => 'cancelled',
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);

        return $txn;
    }

    /**
     * 🟡 كل معاملات العملاء التي تنتظر موافقة Teller
     */
    public function customerPendingTransactions()
    {
        return Transaction::where('metadata->is_customer_transaction', true)
            ->where('status', 'pending')
            ->get();
    }
    public function approveByManager(Transaction $txn, $manager): Transaction
{
    // 1) التحقق من نوع المستخدم
    if ($manager->role !== 'manager') {
        throw new Exception("Only managers can approve this transaction.");
    }

    // 2) التحقق من حالة المعاملة
    if ($txn->status !== 'pending') {
        throw new Exception("Transaction is not awaiting manager approval.");
    }

    // 3) تنفيذ العملية المالية
    if ($txn->type === 'withdrawal') {
        $this->ops->withdraw($txn->fromAccount, $txn->amount);
    }

    if ($txn->type === 'deposit') {
        $this->ops->deposit($txn->toAccount, $txn->amount);
    }

    if ($txn->type === 'transfer') {
        $this->ops->withdraw($txn->fromAccount, $txn->amount);
        $this->ops->deposit($txn->toAccount, $txn->amount);
    }

    // 4) تحديث حالة المعاملة
    $txn->update([
        'status' => 'completed',
        'approved_by' => $manager->id,
        'approved_at' => now(),
        'processed_at' => now(),
    ]);

    // 5) تسجيل موافقة المدير
    $txn->approvals()->create([
        'approver_id' => $manager->id,
        'action' => 'approve',
        'level' => 'manager',
        'comments' => 'Approved by manager',
        'is_required' => true,
        'action_taken_at' => now()
    ]);

    return $txn;
}
public function rejectByManager(Transaction $txn, $manager): Transaction
{
    // 1) التحقق من الصلاحيات
    if ($manager->role !== 'manager') {
        throw new Exception("Only managers can reject this transaction.");
    }

    // 2) التأكد أن المعاملة بانتظار موافقة المدير
    if ($txn->status !== 'pending') {
        throw new Exception("Transaction is not awaiting manager approval.");
    }

    // 3) تحديث حالة العملية
    $txn->update([
        'status' => 'cancelled',
        'approved_by' => $manager->id,
        'approved_at' => now(),
    ]);

    // 4) تسجيل الرفض في جدول approvals
    $txn->approvals()->create([
        'approver_id' => $manager->id,
        'action' => 'reject',
        'level' => 'manager',
        'comments' => 'Rejected by manager',
        'is_required' => true,
        'action_taken_at' => now(),
    ]);

    return $txn;
}

}
