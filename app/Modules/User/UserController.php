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


   // ⬇ تابع 1 — جلب كل العملاء
    public function getAllCustomers()
{
    $user = auth()->user();

    // Admin + Manager + Teller مسموح لهم
    if (!in_array($user->role, ['admin', 'manager', 'teller'])) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $customers = User::where('role', 'customer')
        ->withCount('accounts')
        ->latest()
        ->get();

    return response()->json($customers);
}



// ⬇ تابع 2 — جلب كل الموظفين (مدير + صراف + أي دور غير العميل)
    public function getAllEmployees()
{
    $user = auth()->user();

    // فقط الـ Admin يسمح له
    if ($user->role !== 'admin') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $employees = User::whereIn('role', ['manager', 'teller'])
        ->withCount('accounts')
        ->latest()
        ->get();

    return response()->json($employees);
}



// ⬇ تابع 3 — جلب كل الصرافين فقط (tellers)
    public function getAllTellers()
{
    $user = auth()->user();

    // فقط الـ Manager يسمح له
    if ($user->role !== 'manager') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $tellers = User::where('role', 'teller')
        ->withCount('accounts')
        ->latest()
        ->get();

    return response()->json($tellers);
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
