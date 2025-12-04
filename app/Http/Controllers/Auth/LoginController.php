<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'بيانات الدخول غير صحيحة.',
            ], 401);
        }

        $user = $request->user();

        // التأكد من تحقق الإيميل للمستخدم العادي
        if ($user->role === 'customer' && ! $user->hasVerifiedEmail()) {
            Auth::logout();

            return response()->json([
                'message' => 'يجب تأكيد البريد الإلكتروني قبل تسجيل الدخول.',
            ], 403);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح.',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح.',
        ]);
    }
}
