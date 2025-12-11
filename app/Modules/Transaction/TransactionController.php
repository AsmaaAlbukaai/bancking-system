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
    /**
     * @OA\Post(
     *     path="/api/transactions/transfer",
     *     summary="Transfer money between two accounts",
     *     tags={"Transactions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"from_account_id", "to_account_id", "amount"},
     *             @OA\Property(property="from_account_id", type="integer", example=1),
     *             @OA\Property(property="to_account_id", type="integer", example=2),
     *             @OA\Property(property="amount", type="number", example=1500)
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Transfer successful"),
     *     @OA\Response(response=400, description="Insufficient balance"),
     *     @OA\Response(response=404, description="Account not found"),
     * )
     */


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
    /**
     * @OA\Get(
     *     path="/api/transactions/history/{accountId}",
     *     summary="Get transaction history for a specific account",
     *     tags={"Transactions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="accountId",
     *         in="path",
     *         required=true,
     *         description="Account ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Transaction history"),
     *     @OA\Response(response=404, description="Account not found"),
     * )
     */

    // جلب عمليات حساب معين
    public function history($accountId)
    {
        $sx = Transaction::forAccount($accountId)->latest()->get();
        return response()->json($sx);
    }

    /******************************
     * عمليات الزبون (سحب / إيداع)
     ******************************/

    /**
     * @OA\Post(
     *     path="/api/transaction/{accountId}",
     *     summary="Send customer transaction request (deposit or withdraw)",
     *     tags={"Transactions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="accountId",
     *         in="path",
     *         required=true,
     *         description="Customer account ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type", "amount"},
     *             @OA\Property(property="type", type="string", example="withdraw"),
     *             @OA\Property(property="amount", type="number", example=500),
     *             @OA\Property(property="description", type="string", example="ATM withdrawal")
     *         )
     *     ),
     *
     *     @OA\Response(response=201, description="Transaction request created"),
     *     @OA\Response(response=400, description="Invalid data")
     * )
     */

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
    /**
     * @OA\Post(
     *     path="/api/transactions/approve/{transactionId}",
     *     summary="Approve customer transaction by teller",
     *     tags={"Transactions - Teller"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="transactionId",
     *         in="path",
     *         required=true,
     *         description="Transaction ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Transaction approved"),
     *     @OA\Response(response=404, description="Transaction not found")
     * )
     */

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

    /**
     * @OA\Post(
     *     path="/api/transactions/reject/{transactionId}",
     *     summary="Reject customer transaction by teller",
     *     tags={"Transactions - Teller"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="transactionId",
     *         in="path",
     *         required=true,
     *         description="Transaction ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", example="Invalid request")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Transaction rejected")
     * )
     */

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

    /**
     * @OA\Post(
     *     path="/api/transactions/approve/manager/{id}",
     *     summary="Approve transaction by bank manager",
     *     tags={"Transactions - Manager"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Transaction ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=200, description="Transaction approved by manager"),
     *     @OA\Response(response=404, description="Transaction not found")
     * )
     */

   public function approveByManager($txnId)
   {
    $txn = Transaction::findOrFail($txnId);
    return response()->json(
        $this->bank->approveByManager($txn, auth()->user())
    );
   }
    /**
     * @OA\Post(
     *     path="/api/transactions/reject/manager/{id}",
     *     summary="Reject transaction by bank manager",
     *     tags={"Transactions - Manager"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Transaction ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", example="Suspicious activity detected")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Transaction rejected by manager")
     * )
     */

    public function rejectByManager($txnId)
{
    $txn = Transaction::findOrFail($txnId);

    return response()->json(
        $this->bank->rejectByManager($txn, auth()->user())
    );
}
    /**
     * @OA\Get(
     *     path="/api/customer-transactions/requests",
     *     summary="Get all pending customer transaction requests",
     *     tags={"Transactions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Pending requests list")
     * )
     */

public function customerRequests(Request $request)
{
    $user = auth()->user();

    $query = Transaction::query()
        ->where('metadata->is_customer_transaction', true)
        ->with(['fromAccount', 'toAccount', 'approvals'])
        ->latest();

<<<<<<< HEAD
    /**********************************
     *  فلترة حسب دور المستخدم
     **********************************/

=======
>>>>>>> 9f64c40 (new commit)
    if ($user->role === 'teller') {
        $query->whereHas('approvals', function($q) {
            $q->where('level', 'teller')->where('action','review'); // لم يتم اتخاذ إجراء بعد
        });
    }

    if ($user->role === 'manager') {
        $query->whereHas('approvals', function($q) {
            $q->where('level', 'manager')->where('action','review');// لم يتم اتخاذ إجراء بعد
        });
    }

<<<<<<< HEAD
    /**********************************
     *  فلترة إضافية حسب الطلب
     **********************************/

=======
>>>>>>> 9f64c40 (new commit)
    if ($request->has('status')) {
        $query->where('status', $request->status);
    }

    if ($request->has('type')) {
        $query->where('type', $request->type);
    }

    return response()->json($query->get());
}
    /**
     * @OA\Get(
     *     path="/api/transactions/all",
     *     summary="Get all transactions in the system",
     *     tags={"Transactions"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="All transactions data")
     * )
     */

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
