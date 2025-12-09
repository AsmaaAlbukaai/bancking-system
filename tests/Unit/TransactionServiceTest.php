<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\DB;
use App\Modules\Transaction\TransactionService;
use App\Modules\Account\AccountOperationsService;
use App\Modules\Notification\NotificationDispatcher;
use App\Modules\Transaction\Handlers\BaseApprovalHandler;
use App\Modules\Transaction\Transaction;

class TransactionServiceTest extends TestCase
{
    protected $ops;
    protected $notifier;
    protected $approval;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ops = Mockery::mock(AccountOperationsService::class);
        $this->notifier = Mockery::mock(NotificationDispatcher::class);
        $this->approval = Mockery::mock(BaseApprovalHandler::class);

        $this->service = new TransactionService(
            $this->approval,
            $this->notifier,
            $this->ops
        );
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function fakeAccount($id)
    {
        $acc = Mockery::mock('App\Modules\Account\Account')->makePartial();
        $acc->id = $id;
        return $acc;
    }

    private function fakeTransaction(array $data = [])
    {
        $txn = Mockery::mock(Transaction::class)->makePartial();
        $txn->shouldAllowMockingProtectedMethods();

        foreach ($data as $k => $v) {
            $txn->{$k} = $v;
        }

        $txn->shouldReceive('approvals->create')->andReturn(true);
        $txn->shouldReceive('update')->andReturnUsing(function ($arr) use ($txn) {
            foreach ($arr as $k => $v) {
                $txn->{$k} = $v;
            }
            return true;
        });
        $txn->shouldReceive('setAttribute')->passthru();

        return $txn;
    }

    public function test_transfer_is_completed_successfully()
    {
        $from = $this->fakeAccount(1);
        $to = $this->fakeAccount(2);

        $this->ops->shouldReceive('canWithdraw')->with($from, 100)->andReturn(true);
        $this->approval->shouldReceive('handle')->andReturn(true);

        $this->ops->shouldReceive('withdraw')->once()->with($from, 100);
        $this->ops->shouldReceive('deposit')->once()->with($to, 100);

        $txn = $this->fakeTransaction([
            'status' => 'pending',
            'from_account_id' => 1,
            'to_account_id' => 2,
            'amount' => 100,
            'net_amount' => 100
        ]);

        $this->partialMock(Transaction::class, function ($mock) use ($txn) {
            $mock->shouldReceive('create')->andReturn($txn);
        });

        DB::shouldReceive('transaction')->andReturnUsing(fn($cb) => $cb());

        $result = $this->service->transfer($from, $to, 100);

        $this->assertEquals('completed', $result->status);
    }

    public function test_customer_deposit_is_approved_immediately()
    {
        $acc = $this->fakeAccount(1);

        $this->approval->shouldReceive('handle')->andReturn(true);
        $this->ops->shouldReceive('deposit')->once()->with($acc, 200);

        $txn = $this->fakeTransaction(['status' => 'pending']);

        $this->partialMock(Transaction::class, function ($mock) use ($txn) {
            $mock->shouldReceive('create')->andReturn($txn);
        });

        DB::shouldReceive('transaction')->andReturnUsing(fn($cb) => $cb());

        $result = $this->service->customerTransaction($acc, 200, 'deposit');

        $this->assertEquals('completed', $result->status);
    }

    public function test_customer_withdraw_is_rejected_if_no_balance()
    {
        $acc = $this->fakeAccount(1);

        $this->ops->shouldReceive('canWithdraw')->andReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient funds.');

        $this->service->customerTransaction($acc, 200, 'withdrawal');
    }

    public function test_teller_can_approve_transaction()
    {
        $user = (object)['role' => 'teller', 'id' => 10];

        $txn = $this->fakeTransaction([
            'status' => 'pending',
            'type' => 'deposit',
            'amount' => 50,
            'to_account_id' => 1
        ]);

        $txn->toAccount = $this->fakeAccount(1);

        $this->ops->shouldReceive('deposit')->once()->with($txn->toAccount, 50);

        $result = $this->service->approveTransaction($txn, $user);

        $this->assertEquals('completed', $result->status);
    }

    public function test_manager_can_reject_transaction()
    {
        $user = (object)['role' => 'manager', 'id' => 999];

        $txn = $this->fakeTransaction(['status' => 'pending']);

        $result = $this->service->rejectByManager($txn, $user);

        $this->assertEquals('cancelled', $result->status);
    }
}
