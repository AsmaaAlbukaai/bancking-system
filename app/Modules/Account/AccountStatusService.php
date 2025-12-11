<?php
namespace App\Modules\Account;

use App\Models\AccountStatusChangeRequest;

class AccountStatusService
{
    public function requestStatusChange(Account $acc, string $newStatus)
    {
        $needsManager = in_array($newStatus, ['frozen', 'closed']);
        $requester = auth()->user();
        return AccountStatusChangeRequest::create([
            'account_id' => $acc->id,
            'requested_status' => $newStatus,
            'current_status' => $acc->status,
            'approval_level' => 'teller',
            'status' => 'pending',
            'requested_by' => $requester->id,
        ]);
    }

    public function approve(AccountStatusChangeRequest $req, $user)
    {
        
        $req->status = 'approved';
        $req->approved_by = $user->id;
        $req->save();

        $acc = $req->account;
        $acc->status = $req->requested_status;
        $acc->save();

        return $acc;
    }
}
