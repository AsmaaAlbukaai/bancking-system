<?php

namespace App\Services\Account\Strategies;

use App\Models\Account;

class SavingsStrategy implements AccountStrategy
{
    public function canWithdraw(Account $account, float $amount): bool
    {
        return $account->balance - $amount >= $account->minimum_balance;
    }

    public function withdraw(Account $account, float $amount): void
    {
        $account->balance -= $amount;
        $account->save();
    }

    public function deposit(Account $account, float $amount): void
    {
        $account->balance += $amount;
        $account->save();
    }
}
