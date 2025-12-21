<?php

namespace App\Modules\Notification;

use App\Models\User;

class DomainEventNotifier
{
    protected NotificationDispatcher $dispatcher;

    public function __construct(NotificationDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function transactionApprovedForCustomer(User $customer, string $type, float $amount): void
    {
        $this->dispatcher->dispatch(
            $customer,
            'Transaction Approved',
            "Your {$type} transaction of {$amount} has been approved.",
            [
                'event' => 'transaction_approved',
                'type'  => $type,
                'amount'=> $amount
            ]
        );
    }

    public function transactionRequestCreatedForStaff(User $staff, string $role, string $type, float $amount): void
    {
        $this->dispatcher->dispatch(
            $staff,
            'New Transaction Request',
            "A {$type} transaction of {$amount} requires your approval.",
            [
                'event' => 'transaction_approval_required',
                'role'  => $role,
                'type'  => $type,
                'amount'=> $amount
            ]
        );
    }
}
