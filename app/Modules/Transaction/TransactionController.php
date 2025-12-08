<?php

namespace App\Modules\Transaction;

use App\Http\Controllers\Controller;
use App\Modules\Account\Account;
use App\Modules\Banking\BankFacade;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected BankFacade $bank;

    public function __construct(BankFacade $bank)
    {
        $this->bank = $bank;
    }

    // تنفيذ تحويل
    public function transfer(Request $request)
    {
        $data = $request->validate([
            'from_account_id' => 'required|integer',
            'to_account_id'   => 'required|integer',
            'amount'          => 'required|numeric|min:1'
        ]);

        $from = Account::findOrFail($data['from_account_id']);
        $to = Account::findOrFail($data['to_account_id']);

        $txn = $this->bank->transfer($from, $to, $data['amount']);

        return response()->json($txn);
    }

    // جلب عمليات حساب معين
    public function history($accountId)
    {
        $sx = Transaction::forAccount($accountId)->latest()->get();
        return response()->json($sx);
    }

    /******************************
     * عمليات الزبون (سحب / إيداع)
     ******************************/

    // 🔹 تنفيذ عملية سحب أو إيداع للزبون
    public function customerTransaction(Request $request, $accountId)
    {
        $data = $request->validate([
            'type' => 'required|in:deposit,withdrawal',
            'amount' => 'required|numeric|min:1'
        ]);

        $account = Account::findOrFail($accountId);

        $txn = $this->bank->customerTransaction(
            $account,
            $data['amount'],
            $data['type'],
            $request->all()
        );

        return response()->json($txn);
    }

    /******************************
     * موافقة الموظف (Teller Approval)
     ******************************/

    // 🔹 موافقة موظف Teller على العملية
    public function approveCustomerTransaction($transactionId)
    {
        $txn = Transaction::findOrFail($transactionId);

        $user = auth()->user();

        // يجب أن يكون Teller
        if ($user->role !== 'teller') {
            return response()->json(['error' => 'Only teller can approve this'], 403);
        }

        $approved = $this->bank->approveTransaction($txn, $user);

        return response()->json($approved);
    }

    // 🔹 رفض موظف Teller للطلب
    public function rejectCustomerTransaction($transactionId)
    {
        $txn = Transaction::findOrFail($transactionId);

        $user = auth()->user();

        if ($user->role !== 'teller') {
            return response()->json(['error' => 'Only teller can reject this'], 403);
        }

        $rejected = $this->bank->rejectTransaction($txn, $user);

        return response()->json(['message' => 'Transaction rejected']);
    }
    
    
   public function approveByManager($txnId)
   {
    $txn = Transaction::findOrFail($txnId);
    return response()->json(
        $this->bank->approveByManager($txn, auth()->user())
    );
   }

    public function rejectByManager($txnId)
{
    $txn = Transaction::findOrFail($txnId);

    return response()->json(
        $this->bank->rejectByManager($txn, auth()->user())
    );
}
public function customerRequests(Request $request)
{
    $user = auth()->user();

    $query = Transaction::query()
        ->where('metadata->is_customer_transaction', true)
        ->with(['fromAccount', 'toAccount', 'approvals'])
        ->latest();

    /********************************** 
     *  فلترة حسب دور المستخدم 
     **********************************/

    if ($user->role === 'teller') {
        // 🔹 الطلبات التي تحتاج موافقة Teller
        $query->where('status', 'pending');
    }

    if ($user->role === 'manager') {
        // 🔹 الطلبات التي تحتاج موافقة Manager
        $query->where('status', 'pending');
    }

    /********************************** 
     *  فلترة إضافية حسب الطلب 
     **********************************/

    if ($request->has('status')) {
        $query->where('status', $request->status);
    }

    if ($request->has('type')) {
        $query->where('type', $request->type);
    }

    return response()->json($query->get());
}

     public function allTransactions()
{
    $user = auth()->user();

    // 🔹 فقط الادمن له حق رؤية جميع المعاملات
    if ($user->role !== 'admin') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $txns = Transaction::with(['fromAccount', 'toAccount', 'approvals'])
        ->latest()
        ->get();

    return response()->json($txns);
    }

}
