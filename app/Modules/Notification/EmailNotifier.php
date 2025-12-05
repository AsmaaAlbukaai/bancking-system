<?php

namespace App\Modules\Notification;
use Illuminate\Support\Facades\Log;

class EmailNotifier implements NotifierInterface
{
    public function notify($user, string $title, string $message, array $data = []): void
    {
        // هنا يمكن استخدام Mailable حقيقي، لكن لأغراض المثال سنستخدم Log
        Log::info("Email to {$user->email}: {$title} - {$message}");
        // Mail::to($user->email)->send(new \App\Mail\GenericNotification($title, $message, $data));
    }
}
