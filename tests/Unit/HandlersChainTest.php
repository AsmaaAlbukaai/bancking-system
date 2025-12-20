<?php

namespace Tests\Unit\Transaction;

use Tests\TestCase;
use App\Modules\Transaction\Handlers\AutoApprovalHandler;
use App\Modules\Transaction\Handlers\TellerApprovalHandler;
use App\Modules\Transaction\Handlers\ManagerApprovalHandler;
use App\Modules\Transaction\Transaction;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\Attributes\Test;

/**
 * بديل آمن لـ addMethods(): كائن بسيط يسجل عدد مرات الاستدعاء لـ create.
 */
class ApprovalsDouble
{
    public int $calls = 0;
    public array $lastPayload = [];
    public function create(array $payload): void
    {
        $this->calls++;
        $this->lastPayload = $payload;
    }
}

class HandlersChainTest extends TestCase
{
    #[Test]
    public function small_amount_is_auto_approved(): void
    {
        /** @var Transaction|MockObject $txn */
        $txn = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['approvals','save'])
            ->getMock();
        $txn->type = 'deposit';
        $txn->amount = 50.0;
        $txn->status = 'pending';

        $approvals = new ApprovalsDouble();
        $txn->method('approvals')->willReturn($approvals);
        $txn->expects($this->once())->method('save');

        $handler = new AutoApprovalHandler(100.0);
        $result = $handler->handle($txn);

        $this->assertTrue($result);
        $this->assertSame('completed', $txn->status);
        $this->assertSame(1, $approvals->calls);
        $this->assertArrayHasKey('action', $approvals->lastPayload);
    }

    #[Test]
    public function medium_amount_requires_teller_review(): void
    {
        /** @var Transaction|MockObject $txn */
        $txn = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['approvals','save'])
            ->getMock();
        $txn->type = 'transfer';
        $txn->amount = 500.0;
        $txn->status = 'pending';

        $approvals = new ApprovalsDouble();
        $txn->method('approvals')->willReturn($approvals);
        $txn->expects($this->once())->method('save');

        $handler = new TellerApprovalHandler(100.01, 1000.0);
        $result = $handler->handle($txn);

        $this->assertFalse($result);
        $this->assertSame('pending', $txn->status);
        $this->assertSame(1, $approvals->calls);
    }

    #[Test]
    public function large_amount_bubbles_to_manager(): void
    {
        /** @var Transaction|MockObject $txn */
        $txn = $this->getMockBuilder(Transaction::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['approvals','save'])
            ->getMock();
        $txn->type = 'withdrawal';
        $txn->amount = 5000.0;
        $txn->status = 'pending';

        $approvals = new ApprovalsDouble();
        $txn->method('approvals')->willReturn($approvals);

        $chain = new AutoApprovalHandler(100.0);
        $chain->setNext(new TellerApprovalHandler(100.01, 1000.0))
              ->setNext(new ManagerApprovalHandler(1000.01));

        $result = $chain->handle($txn);
        $this->assertFalse($result);
        $this->assertSame('pending', $txn->status);
        $this->assertSame(1, $approvals->calls);
    }
}