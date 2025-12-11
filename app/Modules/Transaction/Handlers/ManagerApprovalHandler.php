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
        return in_array($transaction->type, ['deposit', 'withdrawal', 'transfer'])
            && $transaction->amount >= $this->minAmount
            && $transaction->status === 'pending';
    }

    protected function process(Transaction $transaction): bool
    {
        $transaction->approvals()->create([
            'approver_id' => null,
            'action' => 'review',
            'comments' => 'Requires manager approval',
            'level' => 'manager',
            'is_required' => true,
            'action_taken_at' => now()
        ]);

        $transaction->status = 'pending';
        $transaction->save();

        return false;
    }
}
