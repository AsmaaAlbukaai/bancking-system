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

    /**
     * يحسب الفائدة لحساب معيّن باستخدام الإستراتيجية المناسبة
     * بناءً على نوع الحساب أو طريقة محددة (simple/compound).
     */
    public function calculateForAccount(Account $account, int $days, ?string $method = null): InterestCalculation
    {
        $principal = (float) $account->balance;
        $rate = (float) $account->interest_rate;

        $strategy = $this->factory->make($method, $account);
        $interestAmount = $strategy->calculate($principal, $rate, $days);

        // احفظ نتيجة حساب الفائدة
        $calc = InterestCalculation::create([
            'account_id' => $account->id,
            'principal' => $principal,
            'interest_rate' => $rate,
            'calculation_method' => class_basename($strategy),
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
