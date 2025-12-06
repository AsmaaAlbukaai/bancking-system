<?php

namespace App\Services\Transaction\Handlers;

use App\Models\Transaction;

class AutoApprovalHandler extends BaseApprovalHandler
{
    protected float $limit;

    public function __construct(float $limit = 1000.0)
    {
        $this->limit = $limit;
    }

    protected function canHandle(Transaction $transaction): bool
    {
        // إذا المبلغ أقل من الحد الافتراضي ونوع المعاملة يسمح بالموافقة التلقائية
        return $transaction->amount <= $this->limit && $transaction->status === 'pending';
    }

    protected function process(Transaction $transaction): bool
    {
        $transaction->status = 'completed';
        $transaction->approved_at = now();
        $transaction->save();

        // تسجيل موافقة أو إنشاء سجل اعتماد
        $transaction->approvals()->create([
            'approver_id' => null,
            'action' => 'approve',
            'comments' => 'Auto-approved by system',
            'level' => 'teller',
            'is_required' => false,
            'action_taken_at' => now()
        ]);

        return true;
    }
}
