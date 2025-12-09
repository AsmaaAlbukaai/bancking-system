<?php

namespace App\Modules\Transaction\Recurring;

interface FrequencyStrategyInterface
{
    public function getNextRunDate(\DateTime $current): \DateTime;
}