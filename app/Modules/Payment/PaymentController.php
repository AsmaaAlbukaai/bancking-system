<?php

namespace App\Modules\Payment;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Modules\Account\Account;
use App\Modules\Account\AccountService;
use App\Modules\Payment\PaymentService;
use Illuminate\Http\Request;

/**
 * Controller لإدارة عمليات الدفع عبر بوابات الدفع الخارجية.
 * يستخدم Adapter Pattern لربط بوابات مختلفة مع منطق الإيداع.
 */
class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService,
        protected AccountService $accountService
    ) {}

    /**
     * إيداع مبلغ في حساب عبر بوابة دفع خارجية.
     * 
     * @OA\Post(
     *     path="/api/accounts/{accountId}/deposit-gateway/{gatewayId}",
     *     summary="Deposit money via external payment gateway",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="accountId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="gatewayId", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", example=1000),
     *             @OA\Property(property="currency", type="string", example="USD"),
     *             @OA\Property(property="description", type="string", example="Deposit via gateway")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Deposit successful"),
     *     @OA\Response(response=400, description="Invalid request"),
     *     @OA\Response(response=404, description="Account or gateway not found")
     * )
     */
    public function depositViaGateway(Request $request, int $accountId, int $gatewayId)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'nullable|string|max:3',
            'description' => 'nullable|string|max:255',
        ]);

        // جلب الحساب والبوابة
        $account = $this->accountService->getAccountById($accountId);
        if (!$account) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        $gateway = PaymentGateway::where('id', $gatewayId)
            ->where('is_active', true)
            ->firstOrFail();

        // التحقق من الحد الأدنى والأقصى
        if ($data['amount'] < $gateway->min_amount) {
            return response()->json([
                'message' => "Amount must be at least {$gateway->min_amount}"
            ], 400);
        }

        if ($gateway->max_amount && $data['amount'] > $gateway->max_amount) {
            return response()->json([
                'message' => "Amount must not exceed {$gateway->max_amount}"
            ], 400);
        }

        try {
            // تنفيذ الإيداع عبر البوابة
            $gatewayTransaction = $this->paymentService->depositViaGateway(
                $account,
                $gateway,
                (float) $data['amount'],
                [
                    'currency' => $data['currency'] ?? 'USD',
                    'description' => $data['description'] ?? 'Deposit via gateway',
                    'user_id' => $request->user()->id,
                ]
            );

            return response()->json([
                'message' => 'Deposit successful',
                'gateway_transaction' => $gatewayTransaction,
                'account' => [
                    'id' => $account->id,
                    'balance' => $account->fresh()->balance,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Deposit failed',
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * جلب قائمة بوابات الدفع المتاحة.
     * 
     * @OA\Get(
     *     path="/api/payment-gateways",
     *     summary="Get list of available payment gateways",
     *     tags={"Payments"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of gateways")
     * )
     */
    public function listGateways()
    {
        $gateways = PaymentGateway::where('is_active', true)
            ->select('id', 'name', 'code', 'type', 'min_amount', 'max_amount', 'transaction_fee', 'percentage_fee')
            ->get();

        return response()->json($gateways);
    }
}

