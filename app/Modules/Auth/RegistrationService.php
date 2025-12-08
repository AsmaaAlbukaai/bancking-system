<?php

namespace App\Modules\Auth;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
class RegistrationService
{
    public function registerCustomer(array $data): User
    {
        $code = (string) random_int(100000, 999999);

        $user = User::create([
            'name'  => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'role' => 'customer',
            'email_verification_code' => $code,
            'email_verification_expires_at' => now()->addMinutes(15),
        ]);

        Mail::to($user->email)->send(new VerificationCodeMail($code));

        return $user;
    }
}


