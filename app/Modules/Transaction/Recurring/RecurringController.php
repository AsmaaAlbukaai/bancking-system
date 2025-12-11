<?php
namespace App\Modules\Transaction\Recurring;
use App\Http\Controllers\Controller;
use App\Modules\Transaction\Recurring\RecurringRequest; // أو حسب مكان الملف الحقيقي
use App\Modules\Transaction\Recurring\ScheduledTransactionService; // مثال على مكانه

use Illuminate\Http\Request;

class RecurringController extends Controller
{
    // العميل يطلب
    public function requestRecurring(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'customer') {
            return response()->json(['error' => 'Only customers'], 403);
        }

        $data = $request->validate([
            'type' => 'required|in:deposit,withdrawal,transfer',
            'from_account_id' => 'nullable|integer',
            'to_account_id'   => 'nullable|integer',
            'amount' => 'required|numeric|min:1',
            'frequency' => 'required|in:daily,weekly,monthly'
        ]);

        $req = RecurringRequest::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'from_account_id' => $data['from_account_id'],
            'to_account_id' => $data['to_account_id'],
            'amount' => $data['amount'],
            'frequency' => $data['frequency'],
        ]);

        return response()->json(['message' => 'Request submitted', 'request' => $req]);
    }

    // الصراف يشاهد الطلبات
    public function listRequests()
    {
        $user = auth()->user();

        if (!in_array($user->role, ['teller','manager','admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return RecurringRequest::with(['user'])->latest()->get();
    }

    // الصراف يوافق ويقوم بإنشاء العملية
    public function approve($id, ScheduledTransactionService $service)
    {
        $user = auth()->user();

        if ($user->role !== 'teller') {
            return response()->json(['error' => 'Only tellers'], 403);
        }

        $req = RecurringRequest::findOrFail($id);

        // إنشاء عملية
        $txn = $service->createRecurring($req->toArray());

        $req->update([
            'status' => 'approved',
            'approved_by' => $user->id
        ]);

        return response()->json(['message'=>'Approved','transaction'=>$txn]);
    }

    public function reject($id)
    {
        $user = auth()->user();

        if ($user->role !== 'teller') {
            return response()->json(['error' => 'Only tellers'], 403);
        }

        RecurringRequest::findOrFail($id)->update(['status'=>'rejected']);

        return response()->json(['message'=>'Request rejected']);
    }
}
