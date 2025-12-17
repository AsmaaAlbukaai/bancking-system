<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Modules\Interest\Strategies\SimpleInterestStrategy;
use App\Modules\Interest\Strategies\CompoundInterestStrategy;

class StrategyPatternTest extends TestCase
{
    /**
     * يختبر جوهر Strategy Pattern: إمكانية تبديل الخوارزميات
     */
    public function test_strategy_pattern_allows_switching_algorithms(): void
    {
        // Arrange
        $principal = 10000.00;
        $rate = 5.0;
        $days = 365;
        
        // Act - جرب استراتيجيتين مختلفتين
        $simpleStrategy = new SimpleInterestStrategy();
        $compoundStrategy = new CompoundInterestStrategy();
        
        $simpleResult = $simpleStrategy->calculate($principal, $rate, $days);
        $compoundResult = $compoundStrategy->calculate($principal, $rate, $days);
        
        // Assert - نفس المدخلات، نتائج مختلفة
        $this->assertEquals(500.00, $simpleResult);    // Simple: P * r * t
        $this->assertEquals(511.62, $compoundResult);  // Compound: P(1+r/n)^(nt)-P
        
        // الأهم: إظهار أن Strategy Pattern يعمل
        $this->assertNotEquals($simpleResult, $compoundResult, 
            'Different strategies should produce different results - this demonstrates Strategy Pattern');
        
        $this->assertGreaterThan($simpleResult, $compoundResult,
            'Compound interest should be higher than simple interest');
    }
    
    /**
     * يختبر أن كل الاستراتيجيات تنفذ نفس الـ Interface
     */
    public function test_both_strategies_implement_same_interface(): void
    {
        $simple = new SimpleInterestStrategy();
        $compound = new CompoundInterestStrategy();
        
        // التحقق من أنهم ينفذون نفس الـ Interface
        $this->assertInstanceOf(
            'App\Modules\Interest\Strategies\InterestStrategyInterface',
            $simple,
            'SimpleInterestStrategy should implement InterestStrategyInterface'
        );
        
        $this->assertInstanceOf(
            'App\Modules\Interest\Strategies\InterestStrategyInterface',
            $compound,
            'CompoundInterestStrategy should implement InterestStrategyInterface'
        );
    }
    
    /**
     * يختبر حالات خاصة
     */
    public function test_edge_cases(): void
    {
        $strategy = new SimpleInterestStrategy();
        
        // صفر أيام
        $this->assertEquals(0, $strategy->calculate(10000, 5.0, 0));
        
        // صفر نسبة
        $this->assertEquals(0, $strategy->calculate(10000, 0.0, 30));
        
        // تقريب لرقمين عشريين
        $result = $strategy->calculate(10000, 5.0, 33);
        $this->assertEquals(45.21, $result); // 45.20547... مقرب إلى 45.21
    }
}