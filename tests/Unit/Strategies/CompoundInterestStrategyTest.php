<?php

namespace Tests\Unit\Strategies;

use Tests\TestCase;
use App\Modules\Interest\Strategies\CompoundInterestStrategy;

class CompoundInterestStrategyTest extends TestCase
{
    /** @test */
    public function it_calculates_compound_interest_monthly()
    {
        $strategy = new CompoundInterestStrategy(12); // شهري
        
        // P = 10000, r = 5%, n = 12, t = 1
        $interest = $strategy->calculate(10000, 5.0, 365);
        
        // A = P(1 + r/n)^(nt) - P
        // 10000 * (1 + 0.05/12)^(12*1) - 10000 = 511.62
        $this->assertEquals(511.62, $interest);
    }

    /** @test */
    public function it_calculates_compound_interest_quarterly()
    {
        $strategy = new CompoundInterestStrategy(4); // ربع سنوي
        
        $interest = $strategy->calculate(10000, 5.0, 365);
        
        // 10000 * (1 + 0.05/4)^(4*1) - 10000 = 509.45
        $this->assertEquals(509.45, $interest);
    }

    /** @test */
    public function it_calculates_compound_interest_for_partial_year()
    {
        $strategy = new CompoundInterestStrategy(12);
        
        $interest = $strategy->calculate(10000, 5.0, 180); // نصف سنة
        
   
        $this->assertEquals(249.12, $interest);
    }

    /** @test */
    public function it_uses_default_12_compounds_per_year()
    {
        $strategy = new CompoundInterestStrategy(); // قيمة افتراضية
        
        $interest = $strategy->calculate(10000, 5.0, 365);
        
        // نفس حساب الشهري
        $this->assertEquals(511.62, $interest);
    }
}