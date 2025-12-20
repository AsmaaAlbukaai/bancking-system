<?php

namespace Tests\Unit;
use Tests\TestCase;
use App\Modules\Account\Account;
use App\Modules\Account\AccountStrategyFactory;
use App\Modules\Account\Strategies\CheckingStrategy;
use App\Modules\Account\Strategies\SavingsStrategy;
use App\Modules\Account\Strategies\LoanStrategy;
use App\Modules\Account\Strategies\BusinessStrategy;

class AccountStrategyFactoryTest extends TestCase
{
    /** @test */
    public function it_selects_strategy_by_account_type()
    {
        $factory = new AccountStrategyFactory();

        $savings = new Account(['type' => 'savings']);
        $checking = new Account(['type' => 'checking']);
        $loan = new Account(['type' => 'loan']);
        $business = new Account(['type' => 'business']);

        $this->assertInstanceOf(SavingsStrategy::class, $factory->make($savings));
        $this->assertInstanceOf(CheckingStrategy::class, $factory->make($checking));
        $this->assertInstanceOf(LoanStrategy::class, $factory->make($loan));
        $this->assertInstanceOf(BusinessStrategy::class, $factory->make($business));
    }

    /** @test */
    public function default_is_checking_strategy()
    {
        $factory = new AccountStrategyFactory();
        $acc = new Account(['type' => 'unknown']);
        $this->assertInstanceOf(CheckingStrategy::class, $factory->make($acc));
    }
}
