<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Modules\Account\AccountService;
use App\Modules\Account\AccountNumberGeneratorService;
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

        // 3 - mock لـ AccountStateFactory (استخدم Mockery بدل class حقيقي)
        $stateFactory = Mockery::mock('App\Modules\Account\States\AccountStateFactory');
        
        // 4 - أنشئ mock للـ state
        $mockState = Mockery::mock();
        $mockState->shouldReceive('activate');
        $mockState->shouldReceive('getStatus')->andReturn('active');
        
        $stateFactory->shouldReceive('create')
            ->with('active')
            ->andReturn($mockState);

        // 5 - تحديد أن generate() يرجع رقم حساب ثابت
        $generator->shouldReceive('generate')
            ->once()
            ->andReturn('ACC-12345');

        // 6 - تحديد أن repo->create() يُنادى مرة واحدة بالبيانات المطلوبة
        $repo->shouldReceive('create')
            ->once()
            ->with([
                'name' => 'Ansam',
                'email' => 'ansamalmgdlawi@gmail.com',
                'account_number' => 'ACC-12345'
            ])
            ->andReturn((object)['id' => 1, 'account_number' => 'ACC-12345']);

        // 7 - إنشاء السيرفيس
        $service = new AccountService($repo, $generator, $stateFactory);

        // 8 - البيانات الأصلية
        $data = [
            'name' => 'Ansam',
            'email' => 'ansamalmgdlawi@gmail.com'
        ];

        // 9 - تنفيذ الدالة
        $result = $service->createAccount($data);

        // 10 - التحقق من النتيجة
        $this->assertEquals('ACC-12345', $result->account_number);
        
        Mockery::close();
    }
}