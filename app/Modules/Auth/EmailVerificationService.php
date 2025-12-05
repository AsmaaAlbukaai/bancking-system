<?php

namespace App\Modules\Auth;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailVerificationService
{
    /**
     * @OA\Post(
     *     path="/api/verify-code",
     *     summary="Verify email using code",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","code"},
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="code", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Email verified"),
     *     @OA\Response(response=422, description="Invalid code")
     * )
     */

    public function verifyCode(string $email, string $code): void
    {
        $user = User::where('email', $email)->firstOrFail();

        if ($user->email_verified_at) {
            return;
        }

        if (! $user->email_verification_code ||
            $user->email_verification_code !== $code) {
            throw new \InvalidArgumentException('رمز التفعيل غير صحيح.');
        }

        if ($user->email_verification_expires_at?->isPast()) {
            throw new \RuntimeException('انتهت صلاحية رمز التفعيل، الرجاء طلب رمز جديد.');
        }

        $user->forceFill([
            'email_verified_at' => now(),
            'email_verification_code' => null,
            'email_verification_expires_at' => null,
        ])->save();
    }

    /**
     * @OA\Post(
     *     path="/api/verify-code/resend",
     *     summary="Resend verification code",
     *     tags={"Auth"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string")
     *         )
     *     ),
     *
     *     @OA\Response(response=200, description="Code resent")
     * )
     */

    public function resendCode(string $email): void
    {
        $user = User::where('email', $email)->firstOrFail();

        if ($user->email_verified_at) {
            return;
        }

        $code = (string) random_int(100000, 999999);

        $user->forceFill([
            'email_verification_code' => $code,
            'email_verification_expires_at' => now()->addMinutes(15),
        ])->save();

        Mail::to($user->email)->send(new VerificationCodeMail($code));
    }
}


