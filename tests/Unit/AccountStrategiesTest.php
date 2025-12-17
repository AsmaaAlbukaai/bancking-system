<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Modules\Account\Account;
use App\Modules\Account\Strategies\BusinessStrategy;
use App\Modules\Account\Strategies\CheckingStrategy;
use App\Modules\Account\Strategies\LoanStrategy;
use App\Modules\Account\Strategies\SavingsStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountStrategiesTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function business_strategy_allows_withdrawal_when_active_and_sufficient_balance(): void
    {
        $account = Account::factory()->create([
            'status' => 'active',
            'balance' => 1000.00,
            'type' => 'business'
        ]);

        $strategy = new BusinessStrategy();

        $this->assertTrue($strategy->canWithdraw($account, 500));
        $this->assertFalse($strategy->canWithdraw($account, 1500)); // رصيد غير كافي
        $this->assertFalse($strategy->canWithdraw(
            Account::factory()->create(['status' => 'suspended', 'balance' => 1000]), 
            500
        )); // حساب موقوف
    }

    /** @test */
    public function checking_strategy_allows_withdrawal_with_sufficient_balance(): void
    {
        $account = Account::factory()->create([
            'balance' => 1000.00,
            'type' => 'checking'
        ]);

        $strategy = new CheckingStrategy();

        $this->assertTrue($strategy->canWithdraw($account, 500));
        $this->assertFalse($strategy->canWithdraw($account, 1500));
    }

    /** @test */
    public function loan_strategy_never_allows_withdrawals(): void
    {
        $account = Account::factory()->create([
            'balance' => 10000.00,
            'type' => 'loan'
        ]);

        $strategy = new LoanStrategy();

        $this->assertFalse($strategy->canWithdraw($account, 100));
        $this->assertFalse($strategy->canWithdraw($account, 10000));
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Loan accounts cannot withdraw money.");
        
        $strategy->withdraw($account, 100);
    }

    /** @test */
    public function savings_strategy_respects_minimum_balance(): void
    {
        $account = Account::factory()->create([
            'balance' => 1000.00,
            'minimum_balance' => 200.00,
            'type' => 'savings'
        ]);

        $strategy = new SavingsStrategy();

        $this->assertTrue($strategy->canWithdraw($account, 700)); // 1000 - 700 = 300 >= 200 ✓
        $this->assertFalse($strategy->canWithdraw($account, 850)); // 1000 - 850 = 150 < 200 ✗
    }

    /** @test */
    public function all_strategies_implement_account_strategy_interface(): void
    {
        $this->assertInstanceOf(
            'App\Modules\Account\Strategies\AccountStrategy',
            new BusinessStrategy()
        );
        
        $this->assertInstanceOf(
            'App\Modules\Account\Strategies\AccountStrategy',
            new CheckingStrategy()
        );
        
        $this->assertInstanceOf(
            'App\Modules\Account\Strategies\AccountStrategy',
            new LoanStrategy()
        );
        
        $this->assertInstanceOf(
            'App\Modules\Account\Strategies\AccountStrategy',
            new SavingsStrategy()
        );
    }

    /** @test */
    public function deposit_increases_balance_for_all_strategies_except_loan(): void
    {
        // Business
        $businessAccount = Account::factory()->create(['balance' => 1000, 'type' => 'business']);
        $businessStrategy = new BusinessStrategy();
        $businessStrategy->deposit($businessAccount, 500);
        $this->assertEquals(1500.00, $businessAccount->fresh()->balance);

        // Checking
        $checkingAccount = Account::factory()->create(['balance' => 1000, 'type' => 'checking']);
        $checkingStrategy = new CheckingStrategy();
        $checkingStrategy->deposit($checkingAccount, 300);
        $this->assertEquals(1300.00, $checkingAccount->fresh()->balance);

        // Loan (deposit decreases balance because it's paying off debt)
        $loanAccount = Account::factory()->create(['balance' => 5000, 'type' => 'loan']);
        $loanStrategy = new LoanStrategy();
        $loanStrategy->deposit($loanAccount, 1000);
        $this->assertEquals(4000.00, $loanAccount->fresh()->balance); // 5000 - 1000

        // Savings
        $savingsAccount = Account::factory()->create(['balance' => 1000, 'type' => 'savings']);
        $savingsStrategy = new SavingsStrategy();
        $savingsStrategy->deposit($savingsAccount, 200);
        $this->assertEquals(1200.00, $savingsAccount->fresh()->balance);
    }
}