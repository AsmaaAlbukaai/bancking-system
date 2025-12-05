<?php

namespace App\Modules\Auth;

use App\Http\Controllers\Controller;

class RegisterController extends Controller
{
    public function __construct(
        private RegistrationService $registrationService,
    ) {
    }
    /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new customer",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","phone","password"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="phone", type="string"),
     *             @OA\Property(property="password", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Account created, verification code sent"
     *     )
     * )
     */

    public function register(RegisterRequest $request)
    {
        $this->registrationService->registerCustomer($request->validated());

        return response()->json([
            'message' => 'تم إنشاء الحساب، تم إرسال رمز التفعيل إلى بريدك الإلكتروني.',
        ], 201);
    }
}
