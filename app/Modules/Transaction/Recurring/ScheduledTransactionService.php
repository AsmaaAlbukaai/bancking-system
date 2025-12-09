<?php
namespace App\Modules\Transaction\Recurring;

use App\Modules\Transaction\Transaction;
use Carbon\Carbon;

class ScheduledTransactionService
{
    public function createRecurring(array $data): Transaction
    {
        return Transaction::create([
            'from_account_id' => $data['from_account_id'],
            'to_account_id' => $data['to_account_id'],
            'amount' => $data['amount'],
            'type' => $data['type'],
            'status' => 'scheduled',
            'is_recurring' => true,
            'recurring_frequency' => $data['frequency'],
            'next_recurring_at' => $this->nextDate($data['frequency']),
        ]);
    }

    // يحسب موعد التنفيذ القادم حسب frequency
    private function nextDate($frequency)
    {
        return match ($frequency) {
            'daily'   => Carbon::now()->addDay(),
            'weekly'  => Carbon::now()->addWeek(),
            'monthly' => Carbon::now()->addMonth(),
        };
    }
}
