<?php

namespace App\Services\Account\Strategies;

use App\Models\Account;

class LoanStrategy implements AccountStrategy
{
    public function canWithdraw(Account $account, float $amount): bool
    {
        return false; // حساب قرض -> لا يمكن السحب
    }

    public function withdraw(Account $account, float $amount): void
    {
        throw new \Exception("Loan accounts cannot withdraw money.");
    }

    public function deposit(Account $account, float $amount): void
    {
        $account->balance -= $amount; // السداد يقلل من الرصيد المديون
        $account->save();
    }
}
