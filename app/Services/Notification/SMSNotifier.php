<?php

namespace App\Services\Notification;
use Illuminate\Support\Facades\Log;

class SMSNotifier implements NotifierInterface
{
    public function notify($user, string $title, string $message, array $data = []): void
    {
        // لو عندك بوابة SMS هنا تضع استدعاء API
        Log::info("SMS to {$user->phone}: {$message}");
    }
}
