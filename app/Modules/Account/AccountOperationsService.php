<?php

namespace App\Modules\Account;

use App\Modules\Account\Account;

class AccountOperationsService
{
    public function __construct(
        protected AccountStrategyFactory $factory
    ) {}

    public function canWithdraw(Account $account, float $amount): bool
    {
        return $this->factory->make($account)->canWithdraw($account, $amount);
    }

    public function withdraw(Account $account, float $amount): void
    {
        $this->factory->make($account)->withdraw($account, $amount);
    }

    public function deposit(Account $account, float $amount): void
    {
        $this->factory->make($account)->deposit($account, $amount);
    }
}

