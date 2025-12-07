<?php

namespace App\Modules\Interest\Strategies;

class CompoundInterestStrategy implements InterestStrategyInterface
{
    protected int $compoundsPerYear;

    public function __construct(int $compoundsPerYear = 12)
    {
        $this->compoundsPerYear = $compoundsPerYear;
    }

    public function calculate(float $principal, float $annualRate, int $days): float
    {
        $r = $annualRate / 100.0;
        $n = $this->compoundsPerYear;
        $t = $days / 365;
        $amount = $principal * pow(1 + $r / $n, $n * $t);
        return round($amount - $principal, 2);
    }
}
