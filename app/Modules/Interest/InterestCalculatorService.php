<?php

namespace App\Modules\Interest;

use App\Models\InterestCalculation;
use App\Modules\Account\Account;
use App\Modules\Interest\Strategies\InterestStrategyInterface;

class InterestCalculatorService
{
    protected InterestStrategyInterface $strategy;

    public function __construct(InterestStrategyInterface $strategy)
    {
        $this->strategy = $strategy;
    }

    public function calculateForAccount(Account $account, int $days): InterestCalculation
    {
        $principal = (float) $account->balance;
        $rate = (float) $account->interest_rate;

        $interestAmount = $this->strategy->calculate($principal, $rate, $days);

        // احفظ نتيجة حساب الفائدة
        $calc = InterestCalculation::create([
            'account_id' => $account->id,
            'principal' => $principal,
            'interest_rate' => $rate,
            'calculation_method' => get_class($this->strategy),
            'period' => $days,
            'days' => $days,
            'interest_amount' => $interestAmount,
            'total_amount' => $principal + $interestAmount,
            'calculation_date' => now(),
            'is_applied' => false
        ]);

        return $calc;
    }
}
