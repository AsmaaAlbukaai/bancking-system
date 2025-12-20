<?php

namespace App\Modules\Transaction\Handlers;

use App\Modules\Transaction\Transaction;

class AutoApprovalHandler extends BaseApprovalHandler
{
    protected float $limit;

    public function __construct(float $limit = 100.0)
    {
        $this->limit = $limit;
    }

    protected function canHandle(Transaction $transaction): bool
    {
        return in_array($transaction->type, ['deposit', 'withdrawal', 'transfer'])
            && $transaction->amount <= $this->limit
            && $transaction->status === 'pending';
    }

    protected function process(Transaction $transaction): bool
    {
        $transaction->status = 'completed';
        $transaction->approved_at = now();
        $transaction->save();

        $transaction->approvals()->create([
            'approver_id' => null,
            'action' => 'approve',
            'comments' => 'Auto-approved by system',
            'level' => 'director',
            'is_required' => false,
            'action_taken_at' => now()
        ]);

        return true;
    }
}
