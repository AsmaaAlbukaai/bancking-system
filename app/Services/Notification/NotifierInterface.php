<?php

namespace App\Services\Notification;

interface NotifierInterface
{
    public function notify($user, string $title, string $message, array $data = []): void;
}
