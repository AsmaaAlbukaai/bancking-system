<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Transaction\Handlers\AutoApprovalHandler;
use App\Services\Transaction\Handlers\ManagerApprovalHandler;
use App\Services\Transaction\Handlers\BaseApprovalHandler;
use App\Services\Transaction\TransactionService;
use App\Services\Notification\NotificationDispatcher;
use App\Services\Notification\EmailNotifier;
use App\Services\Notification\SMSNotifier;
use App\Services\Notification\InAppNotifier;
use App\Services\Account\AccountCompositeService;
use App\Services\Interest\InterestCalculatorService;
use App\Services\Interest\Strategies\SimpleInterestStrategy;
use App\Services\Interest\Strategies\CompoundInterestStrategy;
use App\Services\Banking\BankFacade;
use App\Services\Interest\Strategies\InterestStrategyInterface;
use App\Services\Notification\NotifierInterface;

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
            $auto = new AutoApprovalHandler(1000.0);
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
            return new TransactionService($chain, $notifier);
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
