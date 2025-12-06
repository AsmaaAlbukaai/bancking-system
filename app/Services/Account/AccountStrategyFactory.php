<?php

namespace App\Services\Account;

use App\Models\Account;
use App\Services\Account\Strategies\{
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
