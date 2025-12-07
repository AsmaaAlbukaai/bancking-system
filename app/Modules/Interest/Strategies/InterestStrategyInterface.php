<?php

namespace App\Modules\Interest\Strategies;

interface InterestStrategyInterface
{
    /**
     * احسب قيمة الفائدة وأرجع مبلغ الفائدة
     */
    public function calculate(float $principal, float $annualRate, int $days): float;
}
