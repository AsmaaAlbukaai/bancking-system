<?php

namespace App\Modules\Interest\Strategies;

class SimpleInterestStrategy implements InterestStrategyInterface
{
    public function calculate(float $principal, float $annualRate, int $days): float
    {
        // فائدة بسيطة = P * r * (days/365)
        $rate = $annualRate / 100.0;
        return round($principal * $rate * ($days / 365), 2);
    }
}
