<?php

namespace App\Modules\Transaction\Recurring;

class MonthlyStrategy implements FrequencyStrategyInterface
{
    public function getNextRunDate(\DateTime $current): \DateTime
    {
        return $current->modify('+1 month');
    }
}
