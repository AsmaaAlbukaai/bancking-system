<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use App\Services\Banking\BankFacade;
use App\Services\Account\AccountService; // تمت إضافتها

class AccountController extends Controller
{
    protected BankFacade $bank;
    protected AccountService $accountService;

    public function __construct(BankFacade $bank, AccountService $accountService)
    {
        $this->bank = $bank;
        $this->accountService = $accountService;
    }

    /**
     * جلب جميع حسابات المستخدم
     */
    public function index(Request $request)
    {
        $user = $request->user();
        return response()->json($user->accounts()->with('children')->get());
    }

    /**
     * جلب حساب واحد
     */
    public function show($id)
    {
        $acc = Account::with(['children', 'group'])->findOrFail($id);
        return response()->json($acc);
    }

    /**
     * إنشاء حساب جديد (مع توليد رقم حساب تلقائي)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:savings,checking,loan,investment,business',
            'account_name' => 'required|string',
            'balance' => 'numeric|min:0',
            'interest_rate' => 'numeric|min:0',
            'credit_limit' => 'numeric|min:0',
            'minimum_balance' => 'numeric|min:0',
            'parent_account_id' => 'nullable|exists:accounts,id',
            'group_id' => 'nullable|exists:account_groups,id',
        ]);

        // إضافة user_id للحساب الجديد
        $data['user_id'] = $request->user()->id;

        // إنشاء الحساب عبر Service الذي يولد رقم حساب تلقائي
        $account = $this->accountService->createAccount($data);

        return response()->json([
            'message' => 'Account created successfully',
            'account' => $account
        ], 201);
    }

    /**
     * تحديث حساب
     */
    public function update(Request $request, $id)
    {
        $acc = Account::findOrFail($id);
        $acc->update($request->all());

        return response()->json($acc);
    }

    /**
     * حذف حساب
     */
    public function destroy($id)
    {
        $acc = Account::findOrFail($id);
        $acc->delete();

        return response()->json(['message' => 'Account deleted']);
    }

    /**
     * الحصول على الرصيد الكلي (Composite)
     */
    public function totalBalance($id)
    {
        $acc = Account::findOrFail($id);
        $total = $this->bank->getTotalBalance($acc);

        return response()->json(['total_balance' => $total]);
    }
}
