<?php

namespace App\Modules\Banking;

use App\Modules\Account\Account;
use App\Modules\Account\AccountCompositeService;
use App\Modules\Interest\InterestCalculatorService;
use App\Modules\Transaction\TransactionService;

class BankFacade
{
    protected TransactionService $txService;
    protected InterestCalculatorService $interestService;
    protected AccountCompositeService $composite;

    public function __construct(
        TransactionService $txService,
        InterestCalculatorService $interestService,
        AccountCompositeService $composite
    ) {
        $this->txService = $txService;
        $this->interestService = $interestService;
        $this->composite = $composite;
    }

    public function transfer(Account $from, Account $to, float $amount, array $meta = [])
    {
        return $this->txService->transfer($from, $to, $amount, $meta);
    }

    public function calculateInterest(Account $account, int $days = 30)
    {
        return $this->interestService->calculateForAccount($account, $days);
    }

    public function getTotalBalance(Account $account): float
    {
        return $this->composite->getTotalBalance($account);
    }
}
