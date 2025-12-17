<?php

namespace App\Modules\Interest;

use App\Models\InterestCalculation;
use App\Modules\Account\Account;
use App\Modules\Interest\InterestStrategyFactory;

class InterestCalculatorService
{
    protected InterestStrategyFactory $factory;

    public function __construct(InterestStrategyFactory $factory)
    {
        $this->factory = $factory;
    }

    public function calculateForAccount(
        Account $account,
        int $days,
        ?string $method = null
    ): InterestCalculation {

        $principal = (float) $account->balance;
        $rate = (float) $account->interest_rate;

        // تحديد طريقة الحساب
        $method = $method ?? 'simple'; // default
        $strategy = $this->factory->make($method, $account);

        $interestAmount = $strategy->calculate($principal, $rate, $days);

        // الضرائب (مثال 5%)
        $taxRate = 0.05;
        $taxAmount = $interestAmount * $taxRate;
        $netInterest = $interestAmount - $taxAmount;

        return InterestCalculation::create([
            'account_id'          => $account->id,
            'principal'           => $principal,
            'interest_rate'       => $rate,
            'calculation_method'  => $method, // ✔ enum صحيح
            'period'              => "{$days} days", // ✔ string
            'days'                => $days,
            'interest_amount'     => $interestAmount,
            'tax_amount'          => $taxAmount,
            'net_interest'        => $netInterest,
            'total_amount'        => $principal + $netInterest,
            'calculation_date'    => now(),
            'applicable_from'     => now(),
            'applicable_to'       => now()->addDays($days),
            'is_applied'          => false,
        ]);
    


    
    }
}
