<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Notification\{
    NotificationDispatcher,
    EmailNotifier,
    InAppNotifier,
    SMSNotifier,
    DomainEventNotifier
};

class NotificationServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Dispatcher الرئيسي
        $this->app->singleton(NotificationDispatcher::class, function () {
            $dispatcher = new NotificationDispatcher();

            // 🔔 تسجيل كل Notifiers
            $dispatcher->register(new EmailNotifier());
            $dispatcher->register(new InAppNotifier());
            $dispatcher->register(new SMSNotifier());

            return $dispatcher;
        });

        // Domain Event Notifier
        $this->app->singleton(DomainEventNotifier::class);
    }
}
