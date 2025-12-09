<?php

namespace App\Modules\Account;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Banking\BankFacade;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    protected AccountService $service;
    protected BankFacade $bank;
     public function __construct(AccountService $service, BankFacade $bank)
    {
        $this->service = $service;
        $this->bank = $bank;
    }

    /**
     * @OA\Get(
     *     path="/api/test",
     *     summary="Test Endpoint to verify Swagger",
     *     tags={"Test"},
     *     @OA\Response(
     *         response=200,
     *         description="Swagger is working!"
     *     )
     * )
     */
    public function testSwagger() {
        return response()->json(["message" => "Swagger OK"]);
    }
    /**
     * @OA\Get(
     *     path="/api/accounts",
     *     summary="Get all user accounts",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of accounts"
     *     )
     * )
     */

    // جلب جميع حسابات المستخدم
    public function index(Request $request)
    {
        $user = $request->user();
        return response()->json($user->accounts()->with('children')->get());
    }

    // جلب حساب واحد
    /**
     * @OA\Get(
     *     path="/api/accounts/{id}",
     *     summary="Get a single account",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Account ID",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="The account data"
     *     )
     * )
     */

    public function show($id)
    {
        $acc = Account::with(['children', 'group'])->findOrFail($id);
        return response()->json($acc);
    }

    // إنشاء حساب جديد
    /**
     * @OA\Post(
     *     path="/api/accounts",
     *     summary="Create a new account",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type","account_name"},
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="account_name", type="string"),
     *             @OA\Property(property="balance", type="number"),
     *             @OA\Property(property="interest_rate", type="number"),
     *             @OA\Property(property="credit_limit", type="number"),
     *             @OA\Property(property="minimum_balance", type="number"),
     *             @OA\Property(property="parent_account_id", type="integer")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Account created"
     *     )
     * )
     */

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

        $account = $this->service->createAccount($data);

        return response()->json($account, 201);
    }

    // تحديث حساب
    /**
     * @OA\Put(
     *     path="/api/accounts/{id}",
     *     summary="Update an account",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="account_name", type="string"),
     *             @OA\Property(property="balance", type="number"),
     *             @OA\Property(property="interest_rate", type="number"),
     *             @OA\Property(property="credit_limit", type="number"),
     *             @OA\Property(property="minimum_balance", type="number")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Account updated"
     *     )
     * )
     */

    public function update(Request $request, $id)
    {
        $data = $request->only([
            'type', 'account_name', 'balance', 'interest_rate',
            'credit_limit', 'minimum_balance'
        ]);

        $account = $this->service->updateAccount($id, $data);

        return response()->json($account);
    }

    // حذف حساب
    /**
     * @OA\Delete(
     *     path="/api/accounts/{id}",
     *     summary="Delete an account",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Account deleted"
     *     )
     * )
     */

    public function destroy($id)
    {
        $acc = Account::findOrFail($id);
        $acc->delete();

        return response()->json(['message' => 'Account deleted']);
    }

    // الحصول على الرصيد الكلي (Composite)
    /**
     * @OA\Get(
     *     path="/api/accounts/{id}/total-balance",
     *     summary="Get total balance including children accounts",
     *     tags={"Accounts"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Total account balance"
     *     )
     * )
     */

  public function totalBalance($id)
    {
        $account = $this->service->getAccountById($id);
        $total = $this->bank->getTotalBalance($account);

        return response()->json(['total_balance' => $total]);
    }

    /**
     * 🔹 جلب حسابات عميل محدد عبر user_id
     */
    public function getCustomerAccountsByUserId($userId)
    {
        $result = $this->service->getCustomerAccountsByUserId($userId);
        return response()->json($result);
    }

    /**
     * 🔹 إغلاق حساب باستخدام الـ State Pattern
     */
    public function deactivateAccount($accountId)
    {
        $result = $this->service->closeAccount($accountId, request('reason'));
        return response()->json($result);
    }

    /**
     * 🔹 إعادة تفعيل حساب باستخدام الـ State Pattern
     */
    public function activateAccount($accountId)
    {
        $result = $this->service->activateAccount($accountId);
        return response()->json($result);
    }
}
