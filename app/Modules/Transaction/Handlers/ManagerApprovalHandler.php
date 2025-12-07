<?php

namespace App\Modules\Transaction\Handlers;

use App\Modules\Transaction\Transaction;

class ManagerApprovalHandler extends BaseApprovalHandler
{
    protected float $minAmount;

    public function __construct(float $minAmount = 1000.01)
    {
        $this->minAmount = $minAmount;
    }

    protected function canHandle(Transaction $transaction): bool
    {
        // مثال: كل المعاملات التي فوق الحد تحتاج لموافقة مدير.
        return $transaction->amount >= $this->minAmount && $transaction->status === 'pending';
    }

    protected function process(Transaction $transaction): bool
    {
        // هنا لا نكمل التنفيذ فعلياً — نضعها كقيد انتظار موافقة المدير
        // سجل طلب موافقة
        $transaction->approvals()->create([
            'approver_id' => null, // لاحقًا يربط بالمدير
            'action' => 'review',
            'comments' => 'Requires manager approval',
            'level' => 'manager',
            'is_required' => true
        ]);

        // نترك الحالة كما هي (pending) أو set to 'awaiting_approval'
        $transaction->status = 'pending';
        $transaction->save();

        return false;
    }
}
