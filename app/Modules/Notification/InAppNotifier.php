<?php

namespace App\Modules\Notification;

use App\Modules\Notification\Notification ;

class InAppNotifier implements NotifierInterface
{
    public function notify($user, string $title, string $message, array $data = []): void
    {
        Notification::create([
            'user_id' => $user->id,
            'type' => 'system',
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'channel' => 'in_app',
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }
}
