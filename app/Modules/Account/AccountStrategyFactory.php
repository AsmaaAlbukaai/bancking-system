<?php

namespace App\Modules\Account;

use App\Modules\Account\Account;
use App\Modules\Account\strategies\
{
    AccountStrategy,
    SavingsStrategy,
    CheckingStrategy,
    LoanStrategy,
    BusinessStrategy
};

class AccountStrategyFactory
{
    public function make(Account $account): AccountStrategy
    {
        return match ($account->type) {
            'savings'   => new SavingsStrategy(),
            'checking'  => new CheckingStrategy(),
            'loan'      => new LoanStrategy(),
            'business'  => new BusinessStrategy(),
            default     => new CheckingStrategy(),
        };
    }
}
