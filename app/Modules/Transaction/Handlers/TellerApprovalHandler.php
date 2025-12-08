<?php

namespace App\Modules\Transaction\Handlers;

use App\Modules\Transaction\Transaction;

class TellerApprovalHandler extends BaseApprovalHandler
{
    protected float $maxAmount;

    public function __construct(float $maxAmount = 999.9)
    {
        $this->maxAmount = $maxAmount;
    }

    protected function canHandle(Transaction $transaction): bool
    {
     return ($transaction->metadata['is_customer_transaction'] ?? false)
        && $transaction->status === 'pending'
        && $transaction->amount <= $this->maxAmount;

    }

    protected function process(Transaction $transaction): bool
    {
        $transaction->approvals()->create([
            'approver_id' => null,
            'action' => 'request',
            'comments' => 'Requires teller approval',
            'level' => 'teller',
            'is_required' => true
        ]);

        $transaction->status = 'pending';
        $transaction->save();

        return false;
    }
}
