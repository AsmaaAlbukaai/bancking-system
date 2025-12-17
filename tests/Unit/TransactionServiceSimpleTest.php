<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use App\Modules\Transaction\TransactionService;
use App\Modules\Account\Account;
use App\Modules\Transaction\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionServiceSimpleTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_it_throws_exception_when_transferring_to_same_account(): void
    {
        $service = new TransactionService(
            Mockery::mock('App\Modules\Transaction\Handlers\BaseApprovalHandler'),
            Mockery::mock('App\Modules\Notification\NotificationDispatcher'),
            Mockery::mock('App\Modules\Account\AccountOperationsService')
        );

        $account = Account::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Cannot transfer to the same account.");

        $service->transfer($account, $account, 100);
    }

    public function test_it_throws_exception_for_invalid_transaction_type(): void
    {
        $service = new TransactionService(
            Mockery::mock('App\Modules\Transaction\Handlers\BaseApprovalHandler'),
            Mockery::mock('App\Modules\Notification\NotificationDispatcher'),
            Mockery::mock('App\Modules\Account\AccountOperationsService')
        );

        $account = Account::factory()->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid transaction type.");

        $service->customerTransaction($account, 100, 'invalid_type');
    }

    public function test_it_uses_db_transaction_for_transfer(): void
    {
        $mockHandler = Mockery::mock('App\Modules\Transaction\Handlers\BaseApprovalHandler');
        $mockOps = Mockery::mock('App\Modules\Account\AccountOperationsService');
        
        $service = new TransactionService(
            $mockHandler,
            Mockery::mock('App\Modules\Notification\NotificationDispatcher'),
            $mockOps
        );

        $from = Account::factory()->create(['balance' => 1000]);
        $to = Account::factory()->create(['balance' => 500]);

        $mockOps->shouldReceive('canWithdraw')->with($from, 100)->andReturn(true);
        
        // المهم: يجب أن يرجع Mock Handler قيمة boolean
        $mockHandler->shouldReceive('handle')->andReturn(true);

        // Mock DB::transaction
        DB::shouldReceive('transaction')->once()->andReturnUsing(function ($callback) {
            return $callback();
        });

        // استخدم partial mock للـ Transaction بدل stdClass
        $mockTransaction = Mockery::mock(Transaction::class)->makePartial();
        $mockTransaction->shouldReceive('create')->andReturn($mockTransaction);
        $mockTransaction->shouldReceive('update')->andReturn(true);
        
        // Bind الـ Mock إلى Container
        $this->app->instance(Transaction::class, $mockTransaction);

        $mockOps->shouldReceive('withdraw')->once()->with($from, 100);
        $mockOps->shouldReceive('deposit')->once()->with($to, 100);

        $result = $service->transfer($from, $to, 100);

        $this->assertEquals('completed', $result->status);
    }

    public function test_it_handles_deposit_transaction_correctly(): void
    {
        $mockHandler = Mockery::mock('App\Modules\Transaction\Handlers\BaseApprovalHandler');
        $mockOps = Mockery::mock('App\Modules\Account\AccountOperationsService');
        
        $service = new TransactionService(
            $mockHandler,
            Mockery::mock('App\Modules\Notification\NotificationDispatcher'),
            $mockOps
        );

        $account = Account::factory()->create(['balance' => 1000]);

        // المهم: ارجع true للموافقة
        $mockHandler->shouldReceive('handle')->andReturn(true);

        DB::shouldReceive('transaction')->andReturnUsing(function ($callback) {
            return $callback();
        });

        // أنشئ mock صحيح للـ Transaction
        $mockTransaction = Mockery::mock(Transaction::class)->makePartial();
        $mockTransaction->shouldReceive('create')->andReturn($mockTransaction);
        $mockTransaction->shouldReceive('update')->andReturn(true);
        
        $this->app->instance(Transaction::class, $mockTransaction);

        $mockOps->shouldReceive('deposit')->once()->with($account, 200);

        $result = $service->customerTransaction($account, 200, 'deposit');

        $this->assertEquals('completed', $result->status);
    }

    public function test_it_throws_exception_for_insufficient_funds_on_withdrawal(): void
    {
        $mockOps = Mockery::mock('App\Modules\Account\AccountOperationsService');
        
        $service = new TransactionService(
            Mockery::mock('App\Modules\Transaction\Handlers\BaseApprovalHandler'),
            Mockery::mock('App\Modules\Notification\NotificationDispatcher'),
            $mockOps
        );

        $account = Account::factory()->create(['balance' => 100]);

        $mockOps->shouldReceive('canWithdraw')->with($account, 200)->andReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Insufficient funds.");

        $service->customerTransaction($account, 200, 'withdrawal');
    }
}