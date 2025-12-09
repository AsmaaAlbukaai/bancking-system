<?php

namespace App\Jobs;

use App\Modules\Transaction\Transaction;
use App\Modules\Account\Account;
use App\Modules\Banking\BankFacade;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRecurringTransactionsJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    protected BankFacade $bank;

    public function __construct()
    {
        $this->bank = app(BankFacade::class);
    }

    public function handle()
    {
        // جلب كل العمليات المجدولة في موعدها
        $dueTxns = Transaction::where('is_recurring', true)
            ->where('next_recurring_at', '<=', now())
            ->where('status', 'scheduled')
            ->get();

        foreach ($dueTxns as $txn) {

            $account = $txn->fromAccount ?? $txn->toAccount;

            // 1️⃣ التحقق من حالة الحساب باستخدام State Pattern
            $state = $account->getstate();

            if ($txn->type === 'withdrawal' && !$state->canWithdraw($account)) {
                continue;
            }

            if ($txn->type === 'deposit' && !$state->canDeposit($account)) {
                continue;
            }

            if ($txn->type === 'transfer' && !$state->canTransfer($account)) {
                continue;
            }

            // 2️⃣ تنفيذ العملية عبر BankFacade
            if ($txn->type === 'withdrawal') {
                $this->bank->customerTransaction($txn->fromAccount, $txn->amount, 'withdrawal');
            }

            if ($txn->type === 'deposit') {
                $this->bank->customerTransaction($txn->toAccount, $txn->amount, 'deposit');
            }

            if ($txn->type === 'transfer') {
                $this->bank->transfer($txn->fromAccount, $txn->toAccount, $txn->amount);
            }

            // 3️⃣ تحديث موعد التنفيذ القادم
            $txn->next_recurring_at = $this->nextDate($txn->recurring_frequency);
            $txn->save();
        }
    }

    private function nextDate(string $frequency)
    {
        return match ($frequency) {
            'daily' => Carbon::now()->addDay(),
            'weekly' => Carbon::now()->addWeek(),
            'monthly' => Carbon::now()->addMonth(),
        };
    }
}
