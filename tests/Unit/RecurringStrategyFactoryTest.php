<?php

namespace Tests\Unit;
use Tests\TestCase;
use App\Modules\Transaction\Recurring\RecurringStrategyFactory;
use App\Modules\Transaction\Recurring\DailyStrategy;
use App\Modules\Transaction\Recurring\WeeklyStrategy;
use App\Modules\Transaction\Recurring\MonthlyStrategy;

class RecurringStrategyFactoryTest extends TestCase
{
    /** @test */
    public function it_builds_frequency_strategies()
    {
        $this->assertInstanceOf(DailyStrategy::class, RecurringStrategyFactory::make('daily'));
        $this->assertInstanceOf(WeeklyStrategy::class, RecurringStrategyFactory::make('weekly'));
        $this->assertInstanceOf(MonthlyStrategy::class, RecurringStrategyFactory::make('monthly'));
    }

    /** @test */
    public function invalid_frequency_throws_exception()
    {
        $this->expectException(\Exception::class);
        RecurringStrategyFactory::make('yearly');
    }
}
