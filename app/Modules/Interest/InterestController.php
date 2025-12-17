<?php

namespace App\Modules\Interest;

use App\Http\Controllers\Controller;
use App\Modules\Account\Account;
use App\Modules\Banking\BankFacade;
use Illuminate\Http\Request;

/**
 * @OA\Tag(
 *     name="Interest",
 *     description="عمليات حساب الفوائد"
 * )
 */
class InterestController extends Controller
{
    protected BankFacade $bank;

    public function __construct(BankFacade $bank)
    {
        $this->bank = $bank;
    }

     /**
     * @OA\Get(
     *     path="/api/interest/{accountId}/calculate",
     *     summary="حساب الفائدة لحساب معين",
     *     description="يقوم بحساب الفائدة بناءً على نوع الحساب والإستراتيجية المحددة",
     *     tags={"Interest"},
     *     @OA\Parameter(
     *         name="accountId",
     *         in="path",
     *         required=true,
     *         description="معرف الحساب",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="days",
     *         in="query",
     *         required=false,
     *         description="عدد الأيام لحساب الفائدة (اختياري - الافتراضي 30)",
     *         @OA\Schema(type="integer", default=30)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="حساب الفائدة الناجح",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="account_id", type="integer", example=123),
     *                 @OA\Property(property="interest_amount", type="number", format="float", example=150.75),
     *                 @OA\Property(property="total_amount", type="number", format="float", example=10150.75),
     *                 @OA\Property(property="calculation_date", type="string", format="date-time"),
     *                 @OA\Property(property="calculation_method", type="string", example="App\\Modules\\Interest\\Strategies\\CompoundInterestStrategy")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="الحساب غير موجود",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Account not found")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="خطأ في الخادم",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     ),
     *     security={
     *         {"bearerAuth": {}}
     *     }
     * )
     */
    public function calculate(Request $request, $accountId)
    {
        $days = (int) $request->input('days', 30);
        $method = $request->input('method'); // optional: simple|compound

        $acc = Account::findOrFail($accountId);
        $calc = $this->bank->calculateInterest($acc, $days, $method);

        return response()->json($calc);
    }
}
