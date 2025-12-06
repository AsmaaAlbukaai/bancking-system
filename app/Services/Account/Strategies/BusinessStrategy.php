<?php

namespace App\Services\Account\Strategies;

use App\Models\Account;

class BusinessStrategy implements AccountStrategy
{
    public function canWithdraw(Account $account, float $amount): bool
    {
        return $account->status === 'active' && $account->balance >= $amount;
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
