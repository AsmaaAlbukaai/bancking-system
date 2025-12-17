<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Modules\Account\Account;
use App\Modules\Account\States\AccountStateFactory;
use App\Modules\Account\States\ActiveState;
use App\Modules\Account\States\SuspendedState;
use App\Modules\Account\States\FrozenState;
use App\Modules\Account\States\ClosedState;

class AccountStatesTest extends TestCase
{
    /** @test */
    public function factory_creates_correct_state_based_on_account_status(): void
    {
        $factory = new AccountStateFactory();

        $activeAccount = new Account(['status' => 'active']);
        $this->assertInstanceOf(ActiveState::class, $factory->make($activeAccount));

        $suspendedAccount = new Account(['status' => 'suspended']);
        $this->assertInstanceOf(SuspendedState::class, $factory->make($suspendedAccount));

        $frozenAccount = new Account(['status' => 'frozen']);
        $this->assertInstanceOf(FrozenState::class, $factory->make($frozenAccount));

        $closedAccount = new Account(['status' => 'closed']);
        $this->assertInstanceOf(ClosedState::class, $factory->make($closedAccount));

        $unknownAccount = new Account(['status' => 'unknown']);
        $this->assertInstanceOf(ActiveState::class, $factory->make($unknownAccount)); // default
    }

    /** @test */
    public function active_state_allows_all_operations(): void
    {
        $state = new ActiveState();
        $account = new Account();

        $this->assertEquals('active', $state->name());
        $this->assertTrue($state->canWithdraw($account));
        $this->assertTrue($state->canDeposit($account));
        $this->assertTrue($state->canTransfer($account));
    }

    /** @test */
    public function suspended_state_blocks_all_operations(): void
    {
        $state = new SuspendedState();
        $account = new Account();

        $this->assertEquals('suspended', $state->name());
        $this->assertFalse($state->canWithdraw($account));
        $this->assertFalse($state->canDeposit($account));
        $this->assertFalse($state->canTransfer($account));
    }

    /** @test */
    public function frozen_state_blocks_all_operations(): void
    {
        $state = new FrozenState();
        $account = new Account();

        $this->assertEquals('frozen', $state->name());
        $this->assertFalse($state->canWithdraw($account));
        $this->assertFalse($state->canDeposit($account));
        $this->assertFalse($state->canTransfer($account));
    }

    /** @test */
    public function closed_state_blocks_all_operations(): void
    {
        $state = new ClosedState();
        $account = new Account();

        $this->assertEquals('closed', $state->name());
        $this->assertFalse($state->canWithdraw($account));
        $this->assertFalse($state->canDeposit($account));
        $this->assertFalse($state->canTransfer($account));
    }

    /** @test */
    public function all_states_implement_account_state_interface(): void
    {
        $this->assertInstanceOf(
            'App\Modules\Account\States\AccountStateInterface',
            new ActiveState()
        );
        
        $this->assertInstanceOf(
            'App\Modules\Account\States\AccountStateInterface',
            new SuspendedState()
        );
        
        $this->assertInstanceOf(
            'App\Modules\Account\States\AccountStateInterface',
            new FrozenState()
        );
        
        $this->assertInstanceOf(
            'App\Modules\Account\States\AccountStateInterface',
            new ClosedState()
        );
    }
}