<?php

namespace App\Modules\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
     // 🔹 عرض الملف الشخصي للعميل
    public function myProfile()
    {
        $user = auth()->user();

        if ($user->role !== 'customer') {
            return response()->json(['error' => 'Only customers can view their profile'], 403);
        }

        return response()->json([
            'user' => $user,
            'accounts' => $user->accounts()->get(),
        ]);
    }


    public function allCustomers()
{
    $user = auth()->user();

    // 🔹 منع العملاء من الوصول لهذا التابع
    if ($user->role === 'customer') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // 🔹 admin → يرى الجميع
    if ($user->role === 'admin') {
       $users = User::where('role', '!=', 'admin')   // 
            ->withCount('accounts')
            ->latest()
            ->get();

        return response()->json($users);
    }

    // 🔹 manager و teller → يرون العملاء فقط
    if (in_array($user->role, ['manager', 'teller'])) {
        $customers = User::where('role', 'customer')
            ->withCount('accounts')
            ->latest()
            ->get();

        return response()->json($customers);
    }
}

    public function deleteEmployee($userId)
{
    $admin = auth()->user();

    $employee = User::findOrFail($userId);

    // منع حذف أدمن أو زبون
    if (!in_array($employee->role, ['teller', 'manager'])) {
        return response()->json(['error' => 'Cannot delete this type of user'], 400);
    }

    $employee->delete();

    return response()->json(['message' => 'Employee deleted successfully']);
}

}
