<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Transaction\Handlers\AutoApprovalHandler;
use App\Modules\Transaction\Handlers\ManagerApprovalHandler;
use App\Modules\Transaction\Handlers\BaseApprovalHandler;
use App\Modules\Transaction\TransactionService;
use App\Modules\Notification\NotificationDispatcher;
use App\Modules\Notification\EmailNotifier;
use App\Modules\Notification\SMSNotifier;
use App\Modules\Notification\InAppNotifier;
use App\Modules\Account\AccountCompositeService;
use App\Modules\Account\AccountOperationsService;
use App\Modules\Interest\InterestCalculatorService;
use App\Modules\Interest\Strategies\SimpleInterestStrategy;
use App\Modules\Interest\Strategies\CompoundInterestStrategy;
use App\Modules\Banking\BankFacade;
use App\Modules\Interest\Strategies\InterestStrategyInterface;
use App\Modules\Notification\NotifierInterface;
use App\Modules\Transaction\Handlers\TellerApprovalHandler;

class BankingServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Strategies - يمكنك تغيير الافتراضي من هنا
        $this->app->bind(InterestStrategyInterface::class, SimpleInterestStrategy::class);

        // Notification dispatcher و تسجيل المرسلات
        $this->app->singleton(NotificationDispatcher::class, function ($app) {
            $dispatcher = new NotificationDispatcher();
            // سجّل المرسلات المطلوبة
            $dispatcher->register(new InAppNotifier());
            $dispatcher->register(new EmailNotifier());
            $dispatcher->register(new SMSNotifier());
            return $dispatcher;
        });

        // Approval chain
        $this->app->singleton(BaseApprovalHandler::class, function ($app) {
            $auto = new AutoApprovalHandler(100.0);
            $teller = new TellerApprovalHandler(999.9);
            $manager = new ManagerApprovalHandler(1000.01);
            $auto->setNext($manager);
            // يمكنك إضافة مزيد من السلسلة
            return $auto;
        });

        // الخدمات الأساسية
        $this->app->singleton(AccountCompositeService::class);
        $this->app->singleton(InterestCalculatorService::class, function ($app) {
            $strategy = $app->make(InterestStrategyInterface::class);
            return new InterestCalculatorService($strategy);
        });


    $this->app->singleton(TransactionService::class, function ($app) {
    $chain = $app->make(BaseApprovalHandler::class);
    $notifier = $app->make(NotificationDispatcher::class);
    $ops = $app->make(AccountOperationsService::class);

    return new TransactionService($chain, $notifier, $ops);
});


        // Facade service
        $this->app->singleton(BankFacade::class, function ($app) {
            return new BankFacade(
                $app->make(TransactionService::class),
                $app->make(InterestCalculatorService::class),
                $app->make(AccountCompositeService::class)
            );
        });
    }

    public function boot()
    {
        //
    }
}
