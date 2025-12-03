<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use App\Services\Banking\BankFacade;

class AccountController extends Controller
{
    protected BankFacade $bank;

    public function __construct(BankFacade $bank)
    {
        $this->bank = $bank;
    }

    // جلب جميع حسابات المستخدم
    public function index(Request $request)
    {
        $user = $request->user();
        return response()->json($user->accounts()->with('children')->get());
    }

    // جلب حساب واحد
    public function show($id)
    {
        $acc = Account::with(['children', 'group'])->findOrFail($id);
        return response()->json($acc);
    }

    // إنشاء حساب جديد
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string',
            'account_name' => 'required|string',
            'balance' => 'numeric',
            'interest_rate' => 'numeric',
            'credit_limit' => 'numeric',
            'minimum_balance' => 'numeric',
            'parent_account_id' => 'nullable|integer'
        ]);

        $data['user_id'] = $request->user()->id;

        $acc = Account::create($data);

        return response()->json($acc, 201);
    }

    // تحديث حساب
    public function update(Request $request, $id)
    {
        $acc = Account::findOrFail($id);
        $acc->update($request->all());

        return response()->json($acc);
    }

    // حذف حساب
    public function destroy($id)
    {
        $acc = Account::findOrFail($id);
        $acc->delete();

        return response()->json(['message' => 'Account deleted']);
    }

    // الحصول على الرصيد الكلي (Composite)
    public function totalBalance($id)
    {
        $acc = Account::findOrFail($id);
        $total = $this->bank->getTotalBalance($acc);

        return response()->json(['total_balance' => $total]);
    }
}
