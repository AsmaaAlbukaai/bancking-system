<?php

namespace App\Services\Notification;

use App\Models\Notification as NotificationModel;

class InAppNotifier implements NotifierInterface
{
    public function notify($user, string $title, string $message, array $data = []): void
    {
        NotificationModel::create([
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
