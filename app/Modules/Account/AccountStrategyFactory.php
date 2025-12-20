<?php

namespace App\Modules\Account;

use App\Modules\Account\Account;

use App\Modules\Account\Strategies\AccountStrategy;
use App\modules\Account\Strategies\BusinessStrategy;
use App\modules\Account\Strategies\CheckingStrategy;
use App\modules\Account\Strategies\LoanStrategy;
use App\modules\Account\Strategies\SavingsStrategy;

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
