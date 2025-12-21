<?php

namespace App\Modules\Notification;

use Illuminate\Support\Facades\Log;

class SMSNotifier implements NotifierInterface
{
    public function notify($user, string $title, string $message, array $data = []): void
    {
        // هنا يمكنك استدعاء بوابة SMS API
        Log::info("SMS to {$user->phone}: {$message}");
    }
}
