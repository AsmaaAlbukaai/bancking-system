<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResendCodeRequest;
use App\Http\Requests\Auth\VerifyCodeRequest;
use App\Services\Auth\EmailVerificationService;

class VerifyCodeController extends Controller
{
    public function __construct(
        private EmailVerificationService $service,
    ) {
    }

    public function verify(VerifyCodeRequest $request)
    {
        try {
            $this->service->verifyCode(
                $request->input('email'),
                $request->input('code'),
            );

            return response()->json([
                'message' => 'تم تفعيل البريد الإلكتروني بنجاح، يمكنك الآن تسجيل الدخول.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function resend(ResendCodeRequest $request)
    {
        $this->service->resendCode($request->input('email'));

        return response()->json([
            'message' => 'تم إرسال رمز تفعيل جديد إلى بريدك الإلكتروني.',
        ]);
    }
}
