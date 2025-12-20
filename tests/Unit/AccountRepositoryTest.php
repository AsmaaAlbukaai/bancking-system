<?php

namespace Tests\Unit;
use Tests\TestCase;
use App\Repositories\AccountRepository;
use App\Modules\Account\Account;
use PHPUnit\Framework\MockObject\MockObject;

class AccountRepositoryTest extends TestCase
{
    /**
     * نختبر منطق التحقق من عدم وجود الحساب في update/delete بدون لمس قاعدة البيانات.
     */
    public function test_update_throws_if_not_found()
    {
        $repo = $this->getMockBuilder(AccountRepository::class)
            ->onlyMethods(['find'])
            ->getMock();
        $repo->method('find')->willReturn(null);
        $this->expectException(\RuntimeException::class);
        $repo->update(999, ['account_name' => 'X']);
    }

    public function test_delete_throws_if_not_found()
    {
        $repo = $this->getMockBuilder(AccountRepository::class)
            ->onlyMethods(['find'])
            ->getMock();
        $repo->method('find')->willReturn(null);
        $this->expectException(\RuntimeException::class);
        $repo->delete(999);
    }

    public function test_update_happy_path_without_db_side_effects()
    {
        $repo = $this->getMockBuilder(AccountRepository::class)
            ->onlyMethods(['find'])
            ->getMock();
        /** @var Account|MockObject $account */
        $account = $this->getMockBuilder(Account::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['update'])
            ->getMock();
        $account->expects($this->once())->method('update')->with(['account_name' => 'New']);
        $repo->method('find')->willReturn($account);
        $res = $repo->update(1, ['account_name' => 'New']);
        $this->assertSame($account, $res);
    }
}
