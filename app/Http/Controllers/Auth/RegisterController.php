<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\Auth\RegistrationService;

class RegisterController extends Controller
{
    public function __construct(
        private RegistrationService $registrationService,
    ) {
    }

    public function register(RegisterRequest $request)
    {
        $this->registrationService->registerCustomer($request->validated());

        return response()->json([
            'message' => 'تم إنشاء الحساب، تم إرسال رمز التفعيل إلى بريدك الإلكتروني.',
        ], 201);
    }
}
