<?php

namespace App\Modules\Notification;

use App\Models\User;

/**
 * طبقة وسيطة تمثّل Observer على أحداث الدومين
 * بدلاً من استدعاء NotificationDispatcher مباشرةً من كل خدمة.
 */
class DomainEventNotifier
{
    public function __construct(
        protected NotificationDispatcher $dispatcher
    ) {}

    public function transactionApprovedForCustomer(User $customer, string $type, float $amount): void
    {
        $title = 'Transaction Approved';
        $message = "Your {$type} transaction of {$amount} has been approved.";

        $this->dispatcher->dispatch($customer, $title, $message, [
            'event' => 'transaction_approved',
            'transaction_type' => $type,
            'amount' => $amount,
        ]);
    }

    public function transactionRequestCreatedForStaff(User $staff, string $role, string $type, float $amount): void
    {
        $title = 'New Transaction Request';
        $message = "A {$type} transaction of {$amount} requires your approval ({$role}).";

        $this->dispatcher->dispatch($staff, $title, $message, [
            'event' => 'transaction_approval_required',
            'transaction_type' => $type,
            'amount' => $amount,
            'role' => $role,
        ]);
    }

    public function accountStatusApproved(User $owner, string $from, string $to): void
    {
        $title = 'Account Status Updated';
        $message = "Your account status has been changed from {$from} to {$to}.";

        $this->dispatcher->dispatch($owner, $title, $message, [
            'event' => 'account_status_changed',
            'from' => $from,
            'to'   => $to,
        ]);
    }

    public function accountStatusRequestForStaff(User $staff, string $role, string $requestedStatus): void
    {
        $title = 'New Account Status Change Request';
        $message = "A request to change account status to {$requestedStatus} requires your approval ({$role}).";

        $this->dispatcher->dispatch($staff, $title, $message, [
            'event' => 'account_status_approval_required',
            'requested_status' => $requestedStatus,
            'role' => $role,
        ]);
    }
}


