<?php

namespace Tests\Unit\Strategies;

use Tests\TestCase;
use App\Modules\Interest\Strategies\SimpleInterestStrategy;

class SimpleInterestStrategyTest extends TestCase
{
    protected SimpleInterestStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new SimpleInterestStrategy();
    }

    /** @test */
    public function it_calculates_simple_interest_for_30_days()
    {
        // P = 10000, r = 5%, t = 30/365
        $interest = $this->strategy->calculate(10000, 5.0, 30);
        
        $expected = round(10000 * 0.05 * (30/365), 2); // 41.10
        $this->assertEquals($expected, $interest);
    }

    /** @test */
    public function it_calculates_simple_interest_for_365_days()
    {
        // سنة كاملة
        $interest = $this->strategy->calculate(10000, 5.0, 365);
        
        $expected = round(10000 * 0.05 * 1, 2); // 500.00
        $this->assertEquals($expected, $interest);
    }

    /** @test */
    public function it_calculates_zero_interest_for_zero_days()
    {
        $interest = $this->strategy->calculate(10000, 5.0, 0);
        $this->assertEquals(0.00, $interest);
    }

    /** @test */
    public function it_calculates_zero_interest_for_zero_rate()
    {
        $interest = $this->strategy->calculate(10000, 0.0, 30);
        $this->assertEquals(0.00, $interest);
    }

    /** @test */
    public function it_rounds_to_two_decimal_places()
    {
        $interest = $this->strategy->calculate(10000, 5.0, 33);
        // 10000 * 0.05 * (33/365) = 45.205479...
        $this->assertEquals(45.21, $interest); // مقرب
    }
}