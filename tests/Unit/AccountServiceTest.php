<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\modules\Account\AccountService;
use App\modules\Account\AccountNumberGeneratorService;
use App\Repositories\AccountRepository;
use Mockery;

class AccountServiceTest extends TestCase
{
    public function test_create_account()
    {
        // 1 - نعمل mock للريبو
        $repo = Mockery::mock(AccountRepository::class);

        // 2 - mock للرقم التلقائي
        $generator = Mockery::mock(AccountNumberGeneratorService::class);

        // 3 - تحديد أن generate() يرجع رقم حساب ثابت
        $generator->shouldReceive('generate')
            ->once()
            ->andReturn('ACC-12345');

        // 4 - تحديد أن repo->create() يُنادى مرة واحدة بالبيانات المطلوبة
        $repo->shouldReceive('create')
            ->once()
            ->with([
                'name' => 'Ansam',
                'email' => 'ansamalmgdlawi@gmail.com',
                'account_number' => 'ACC-12345'
            ])
            ->andReturn('created');

        // 5 - إنشاء السيرفيس بالمـوك
        $service = new AccountService($repo, $generator);

        // 6 - البيانات الأصلية بدون رقم حساب
        $data = [
            'name' => 'Ansam',
            'email' => 'ansamalmgdlawi@gmail.com'
        ];

        // 7 - تنفيذ الدالة
        $result = $service->createAccount($data);

        // 8 - التحقق من النتيجة
        $this->assertEquals('created', $result);
    }
}
