<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Modules\Banking\BankFacade;
use App\Modules\Account\Account;
use App\Modules\Transaction\TransactionService;
use App\Modules\Interest\InterestCalculatorService;
use App\Modules\Account\AccountCompositeService;
use App\Modules\Transaction\Transaction;
use PHPUnit\Framework\MockObject\MockObject;

class BankFacadeTest extends TestCase
{
    /** @test */
    public function transfer_delegates_to_transaction_service()
    {
        /** @var TransactionService|MockObject $tx */
        $tx = $this->getMockBuilder(TransactionService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['transfer'])
            ->getMock();
        $interest = $this->getMockBuilder(InterestCalculatorService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $composite = $this->getMockBuilder(AccountCompositeService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $from = new Account(['balance' => 100]);
        $to   = new Account(['balance' => 0]);

        // TransactionService::transfer يُرجِع Transaction وليس bool
        $returnedTxn = new Transaction(['status' => 'completed']);
        $tx->expects($this->once())
           ->method('transfer')
           ->with($from, $to, 10.0, [])
           ->willReturn($returnedTxn);

        $facade = new BankFacade($tx, $interest, $composite);
        $res = $facade->transfer($from, $to, 10.0, []);

        $this->assertInstanceOf(Transaction::class, $res);
        $this->assertSame('completed', $res->status);
    }

    /** @test */
    public function get_total_balance_uses_composite_service()
    {
        $tx = $this->getMockBuilder(TransactionService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $interest = $this->getMockBuilder(InterestCalculatorService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $composite = $this->getMockBuilder(AccountCompositeService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['loadTree','getTotalBalance'])
            ->getMock();

        $acc = new Account(['balance' => 10]);
        $composite->expects($this->once())
                  ->method('loadTree')
                  ->with($acc);
        $composite->expects($this->once())
                  ->method('getTotalBalance')
                  ->with($acc)
                  ->willReturn(99.0);

        $facade = new BankFacade($tx, $interest, $composite);
        $this->assertSame(99.0, $facade->getTotalBalance($acc));
    }
}
