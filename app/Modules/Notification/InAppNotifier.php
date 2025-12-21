<?php

namespace App\Modules\Notification;

class InAppNotifier implements NotifierInterface
{
    public function notify($user, string $title, string $message, array $data = []): void
    {
        Notification::create([
            'user_id' => $user->id,
            'title'   => $title,
            'message' => $message,
            'data'    => $data,
            'type'    => 'transaction_approved',
            'channel' => 'in_app',
            'status'  => 'sent',
            'sent_at' => now()
        ]);
    }
}
