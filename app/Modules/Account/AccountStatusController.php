<?php

namespace App\Modules\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AccountStatusChangeRequest;
use App\Modules\Account\Account;
use App\Models\User;
use App\Modules\Account\AccountStatusService;

class AccountStatusController extends Controller
{
    protected AccountStatusService $service;

    public function __construct(AccountStatusService $service)
    {
        $this->service = $service;
    }

    // تقديم طلب
    public function requestChange($accountId, Request $request)
    {
        $request->validate([
            'requested_status' => 'required|in:active,suspended,frozen,closed,dormant'
        ]);

        $acc = Account::findOrFail($accountId);

        $req = $this->service->requestStatusChange($acc, $request->requested_status);

        return response()->json($req);
    }

    // جميع الطلبات
    public function index()
    {
        return AccountStatusChangeRequest::with('account','approver') 
        ->where('approval_level', 'teller')
        ->where('status','pending')
        ->with('account')
        ->latest()
        ->get();
    }

    // تفاصيل طلب
    public function show($id)
    {
        return AccountStatusChangeRequest::with('account','approver')->findOrFail($id);
    }

    // موافقة طلب
    public function approve($id)
    {
        $req = AccountStatusChangeRequest::findOrFail($id);
        $user = auth()->user();

        $result = $this->service->approve($req, $user);

        return response()->json($result);
    }

    // رفض الطلب
    public function reject($id)
    {
        $req = AccountStatusChangeRequest::findOrFail($id);
        $req->status = 'rejected';
        $req->approved_by = auth()->id();
        $req->save();

        return response()->json(['message' => 'Request rejected']);
    }

    // إلغاء من العميل
    public function cancel($id)
    {
        $req = AccountStatusChangeRequest::findOrFail($id);

        if ($req->status !== 'pending') {
            return response()->json(['error' => 'Cannot cancel processed request'], 400);
        }

        $req->status = 'cancelled';
        $req->save();

        return response()->json(['message' => 'Request cancelled']);
    }

    public function myRequests()
{
    $userId = auth()->id();

    return AccountStatusChangeRequest::where('requested_by', $userId)
        ->with('account')
        ->latest()
        ->get();
}

// جميع الطلبات لحساب معيّن
public function accountRequests($accountId)
{
    return AccountStatusChangeRequest::where('account_id', $accountId)
        ->with('account')
        ->latest()
        ->get();
}
}
