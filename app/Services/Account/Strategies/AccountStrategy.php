<?php

namespace App\Services\Account\Strategies;

use App\Models\Account;

interface AccountStrategy
{
    public function canWithdraw(Account $account, float $amount): bool;
    public function withdraw(Account $account, float $amount): void;
    public function deposit(Account $account, float $amount): void;
}

