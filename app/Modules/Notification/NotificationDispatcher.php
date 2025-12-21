<?php

namespace App\Modules\Notification;

use Illuminate\Support\Facades\Log;

class NotificationDispatcher
{
    protected array $notifiers = [];

    public function register(NotifierInterface $notifier): void
    {
        $this->notifiers[] = $notifier;
    }

    public function dispatch($user, string $title, string $message, array $data = []): void
    {
        foreach ($this->notifiers as $notifier) {
            try {
                $notifier->notify($user, $title, $message, $data);
            } catch (\Throwable $e) {
                Log::error('Notification failed: '.$e->getMessage());
            }
        }
    }
}
