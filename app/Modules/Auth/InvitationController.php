<?php


namespace App\Modules\Auth;

use App\Http\Controllers\Controller;
use App\Mail\StaffInvitationMail;
use App\Models\Invitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str; 

class InvitationController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'unique:users,email', 'unique:invitations,email'],
            'role'  => ['required', 'in:teller,manager'],
        ]);

        $token = Str::random(40);

        $invitation = Invitation::create([
            'email'      => $data['email'],
            'role'       => $data['role'],
            'token'      => $token,
            'expires_at' => Carbon::now()->addDays(7),
        ]);

        Mail::to($invitation->email)->send(new StaffInvitationMail($invitation));

        return response()->json([
            'message'    => 'تم إرسال الدعوة بنجاح.',
            'invitation' => $invitation,
        ], 201);
    }

    public function accept(Request $request, string $token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('used', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->firstOrFail();

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'phone'    => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $invitation->email,
            'phone'    => $data['phone'],
            'password' => Hash::make($data['password']),
            'role'     => $invitation->role,
            'email_verified_at' => now(),
        ]);

        $invitation->update(['used' => true]);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'تم إنشاء حساب الموظف وتسجيل الدخول.',
            'token'   => $token,
            'user'    => $user,
        ], 201);
    }
}
