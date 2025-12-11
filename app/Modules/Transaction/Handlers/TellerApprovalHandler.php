<?php

namespace App\Modules\Transaction\Handlers;

use App\Modules\Transaction\Transaction;

class TellerApprovalHandler extends BaseApprovalHandler
{
    protected float $minAmount;
    protected float $maxAmount;

    public function __construct(float $minAmount = 100.01, float $maxAmount = 1000.0)
    {
        $this->minAmount = $minAmount;
        $this->maxAmount = $maxAmount;
    }

    protected function canHandle(Transaction $transaction): bool
    {
        return in_array($transaction->type, ['deposit', 'withdrawal', 'transfer'])
            && $transaction->amount >= $this->minAmount
            && $transaction->amount <= $this->maxAmount
            && $transaction->status === 'pending';
    }

    protected function process(Transaction $transaction): bool
    {
        $transaction->approvals()->create([
            'approver_id' => null,
            'action' => 'review',
            'comments' => 'Requires teller approval',
            'level' => 'teller',
            'is_required' => true,
            'action_taken_at' => now()
        ]);

        $transaction->status = 'pending';
        $transaction->save();

        return false;
    }
}
