<?php

namespace Tests\Unit\Transaction;

use Tests\TestCase;
use Mockery;
use App\Modules\Transaction\TransactionService;
use App\Modules\Account\Account;
use Illuminate\Support\Facades\DB;
use App\Modules\Transaction\Handlers\BaseApprovalHandler;
use App\Modules\Notification\DomainEventNotifier;
use App\Modules\Account\AccountOperationsService;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TransactionServiceSimpleTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * ينشئ جدول transactions مؤقتاً على SQLite in-memory لتفادي أخطاء QueryException
     */
    protected function ensureTransactionsTable(): void
    {
        if (! Schema::hasTable('transactions')) {
            Schema::create('transactions', function (Blueprint $table) {
                $table->id();
                $table->string('reference');
                $table->unsignedBigInteger('from_account_id')->nullable();
                $table->unsignedBigInteger('to_account_id')->nullable();
                $table->decimal('amount', 12, 2);
                $table->decimal('fee', 12, 2)->default(0);
                $table->decimal('tax', 12, 2)->default(0);
                $table->decimal('net_amount', 12, 2)->default(0);
                $table->string('type');
                $table->string('status');
                $table->json('metadata')->nullable();
                $table->timestamp('processed_at')->nullable();
                $table->timestamps();
            });
        }
    }

    #[Test]
    public function it_throws_exception_when_transferring_to_same_account(): void
    {
        $service = new TransactionService(
            Mockery::mock(BaseApprovalHandler::class),
            Mockery::mock(DomainEventNotifier::class),
            Mockery::mock(AccountOperationsService::class)
        );

        $account = new Account(['balance' => 100]);
        $account->id = 1;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot transfer to the same account.');

        $service->transfer($account, $account, 100);
    }

    #[Test]
    public function it_throws_exception_for_invalid_transaction_type(): void
    {
        $service = new TransactionService(
            Mockery::mock(BaseApprovalHandler::class),
            Mockery::mock(DomainEventNotifier::class),
            Mockery::mock(AccountOperationsService::class)
        );

        $account = new Account(['balance' => 100]);
        $account->id = 2;

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid transaction type.');

        $service->customerTransaction($account, 100, 'invalid_type');
    }

    #[Test]
    public function it_uses_db_transaction_for_transfer(): void
    {
        $mockHandler = Mockery::mock(BaseApprovalHandler::class);
        $mockOps     = Mockery::mock(AccountOperationsService::class);

        $service = new TransactionService(
            $mockHandler,
            Mockery::mock(DomainEventNotifier::class),
            $mockOps
        );

        $from = new Account(['balance' => 1000]);
        $to   = new Account(['balance' => 500]);
        $from->id = 10;
        $to->id   = 11;

        $mockOps->shouldReceive('canWithdraw')->with($from, 100.0)->andReturn(true);
        $mockHandler->shouldReceive('handle')->andReturn(true);

        // أنشئ جدول المعاملات لتعمل عملية create فعلياً على SQLite in-memory
        $this->ensureTransactionsTable();

        // نفّذ المعاملة داخل transaction (يمكن تركها حقيقية أو محاكاتها)
        DB::shouldReceive('transaction')->andReturnUsing(function ($callback) { return $callback(); });

        $mockOps->shouldReceive('withdraw')->once()->with($from, 100.0);
        $mockOps->shouldReceive('deposit')->once()->with($to, 100.0);

        $result = $service->transfer($from, $to, 100.0);
        $this->assertEquals('completed', $result->status);
    }

    #[Test]
    public function it_handles_deposit_transaction_correctly(): void
    {
        $mockHandler = Mockery::mock(BaseApprovalHandler::class);
        $mockOps     = Mockery::mock(AccountOperationsService::class);

        $service = new TransactionService(
            $mockHandler,
            Mockery::mock(DomainEventNotifier::class),
            $mockOps
        );

        $account = new Account(['balance' => 1000]);
        $account->id = 22;

        $mockHandler->shouldReceive('handle')->andReturn(true);

        // جدول المعاملات مطلوب هنا أيضاً
        $this->ensureTransactionsTable();
        DB::shouldReceive('transaction')->andReturnUsing(function ($callback) { return $callback(); });

        $mockOps->shouldReceive('deposit')->once()->with($account, 200.0);

        $result = $service->customerTransaction($account, 200.0, 'deposit');
        $this->assertEquals('completed', $result->status);
    }

    #[Test]
    public function it_throws_exception_for_insufficient_funds_on_withdrawal(): void
    {
        $mockOps = Mockery::mock(AccountOperationsService::class);

        $service = new TransactionService(
            Mockery::mock(BaseApprovalHandler::class),
            Mockery::mock(DomainEventNotifier::class),
            $mockOps
        );

        $account = new Account(['balance' => 100]);
        $account->id = 33;

        $mockOps->shouldReceive('canWithdraw')->with($account, 200.0)->andReturn(false);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient funds.');

        $service->customerTransaction($account, 200.0, 'withdrawal');
    }
}