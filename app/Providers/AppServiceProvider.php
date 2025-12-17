<?php

namespace App\Providers;

use App\Modules\Account\Contracts\AccountRepositoryInterface;
use App\Modules\Payment\Gateways\DummyGatewayAdapter;
use App\Modules\Payment\Gateways\PaymentGatewayAdapterInterface;
use App\Modules\Payment\Gateways\StripeGatewayAdapter;
use App\Repositories\AccountRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // ربط عقد الـ AccountRepository مع تنفيذ Eloquent
        $this->app->bind(AccountRepositoryInterface::class, AccountRepository::class);

        // ربط Adapter بوابات الدفع بتنفيذ افتراضي (Dummy)
        // يمكن التبديل إلى StripeGatewayAdapter::class أو أي Adapter آخر
        // هذا يوضح Adapter Pattern: نفس Interface، تنفيذ مختلف
        $this->app->bind(PaymentGatewayAdapterInterface::class, DummyGatewayAdapter::class);
        // $this->app->bind(PaymentGatewayAdapterInterface::class, StripeGatewayAdapter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

