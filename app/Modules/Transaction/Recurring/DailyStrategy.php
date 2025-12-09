<?php

namespace App\Modules\Transaction\Recurring;

class DailyStrategy implements FrequencyStrategyInterface
{
    public function getNextRunDate(\DateTime $current): \DateTime
    {
        return $current->modify('+1 day');
    }
}
