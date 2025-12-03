<?php

namespace App\Services\Interest\Strategies;

use App\Models\InterestCalculation;

interface InterestStrategyInterface
{
    /**
     * احسب قيمة الفائدة وأرجع مبلغ الفائدة
     */
    public function calculate(float $principal, float $annualRate, int $days): float;
}
