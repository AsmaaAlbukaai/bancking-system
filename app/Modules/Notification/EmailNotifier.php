<?php

namespace App\Modules\Notification;

use Illuminate\Support\Facades\Log;

class EmailNotifier implements NotifierInterface
{
    public function notify($user, string $title, string $message, array $data = []): void
    {
        // هنا يمكنك إضافة Mail::to($user->email)->send(...)
        Log::info("Email to {$user->email}: {$title} - {$message}");
    }
}
