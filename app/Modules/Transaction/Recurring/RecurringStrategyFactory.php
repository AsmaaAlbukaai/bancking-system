<?php

namespace App\Modules\Transaction\Recurring;

class RecurringStrategyFactory
{
    public static function make(string $frequency): FrequencyStrategyInterface
    {
        return match ($frequency) {
            'daily'   => new DailyStrategy(),
            'weekly'  => new WeeklyStrategy(),
            'monthly' => new MonthlyStrategy(),
            default   => throw new \Exception("Invalid recurring frequency")
        };
    }
}
