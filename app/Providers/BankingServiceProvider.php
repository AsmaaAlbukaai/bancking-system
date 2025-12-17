<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Transaction\Handlers\AutoApprovalHandler;
use App\Modules\Transaction\Handlers\ManagerApprovalHandler;
use App\Modules\Transaction\Handlers\BaseApprovalHandler;
use App\Modules\Transaction\TransactionService;
use App\Modules\Notification\NotificationDispatcher;
use App\Modules\Notification\DomainEventNotifier;
use App\Modules\Notification\EmailNotifier;
use App\Modules\Notification\SMSNotifier;
use App\Modules\Notification\InAppNotifier;
use App\Modules\Account\AccountCompositeService;
use App\Modules\Account\AccountOperationsService;
use App\Modules\Interest\InterestCalculatorService;
use App\Modules\Interest\InterestStrategyFactory; // أضف هذا
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
        // 1. قم بتعريف InterestStrategyFactory أولاً
        $this->app->singleton(InterestStrategyFactory::class, function ($app) {
            return new InterestStrategyFactory();
        });

        // 2. استراتيجية الفائدة الافتراضية (يستخدمها الـ Factory)
        $this->app->bind(InterestStrategyInterface::class, SimpleInterestStrategy::class);

        // 3. Notification dispatcher
        $this->app->singleton(NotificationDispatcher::class, function ($app) {
            $dispatcher = new NotificationDispatcher();
            $dispatcher->register(new InAppNotifier());
            $dispatcher->register(new EmailNotifier());
            $dispatcher->register(new SMSNotifier());
            return $dispatcher;
        });

        // 4. Approval chain
        $this->app->singleton(BaseApprovalHandler::class, function ($app) {
            $auto = new AutoApprovalHandler(100.0);
            $teller = new TellerApprovalHandler(100.01, 1000.0);
            $manager = new ManagerApprovalHandler(1000.01);
            
            $auto->setNext($teller)->setNext($manager);
            return $auto;
        });

        // 5. InterestCalculatorService - استخدم الـ Factory
        $this->app->singleton(InterestCalculatorService::class, function ($app) {
            // ✅ هذا هو التصحيح: تمرير الـ Factory بدلاً من الـ Strategy مباشرة
            $factory = $app->make(InterestStrategyFactory::class);
            return new InterestCalculatorService($factory);
        });

        // 6. AccountOperationsService (إذا لم يكن معرفاً)
        $this->app->singleton(AccountOperationsService::class);

        // 7. TransactionService
        $this->app->singleton(TransactionService::class, function ($app) {
            $chain = $app->make(BaseApprovalHandler::class);
            $dispatcher = $app->make(NotificationDispatcher::class);
            $notifier = new DomainEventNotifier($dispatcher);
            $ops = $app->make(AccountOperationsService::class);

            return new TransactionService($chain, $notifier, $ops);
        });

        // 8. BankFacade
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