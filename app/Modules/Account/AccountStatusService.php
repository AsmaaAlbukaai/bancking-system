<?php
namespace App\Modules\Account;

use App\Models\AccountStatusChangeRequest;
use App\Modules\Notification\DomainEventNotifier;

class AccountStatusService
{
    public function __construct(
        protected DomainEventNotifier $notifier
    ) {}
    public function requestStatusChange(Account $acc, string $newStatus)
    {
        $needsManager = in_array($newStatus, ['frozen', 'closed']);
        $requester = auth()->user();
        $req = AccountStatusChangeRequest::create([
            'account_id' => $acc->id,
            'requested_status' => $newStatus,
            'current_status' => $acc->status,
            'approval_level' => $needsManager ? 'manager' : 'teller',
            'status' => 'pending',
            'requested_by' => $requester->id,
        ]);

        // إشعار الموظفين/المديرين بوجود طلب حالة جديد
        $role = $needsManager ? 'manager' : 'teller';
        $this->notifyStaffForStatusRequest($req, $role);

        return $req;
    }

    /**
     * إشعار الموظفين/المديرين بوجود طلب تغيير حالة جديد
     */
    protected function notifyStaffForStatusRequest(AccountStatusChangeRequest $req, string $role): void
    {
        $staffMembers = \App\Models\User::where('role', $role)->get();

        foreach ($staffMembers as $staff) {
            $this->notifier->accountStatusRequestForStaff(
                $staff,
                $role,
                $req->requested_status
            );
        }
    }

    public function approve(AccountStatusChangeRequest $req, $user)
    {
        
        $req->status = 'approved';
        $req->approved_by = $user->id;
        $req->save();

        $acc = $req->account;
        $from = $acc->status;
        $acc->status = $req->requested_status;
        $acc->save();

        // إشعار صاحب الحساب بتغيير الحالة
        if ($acc->user) {
            $this->notifier->accountStatusApproved(
                $acc->user,
                $from,
                $acc->status
            );
        }

        return $acc;
    }
}
