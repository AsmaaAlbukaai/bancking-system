<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\InterestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BankingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Auth\VerifyCodeController;

// Auth: register + login (بدون توكن مسبق)
Route::post('register', [RegisterController::class, 'register'])->name('api.register');
Route::post('/login', [LoginController::class, 'login'])->name('api.login');
Route::post('/email/verify-code', [VerifyCodeController::class, 'verify'])->name('api.verify-code');
Route::post('/email/resend-code', [VerifyCodeController::class, 'resend'])->name('api.resend-code');


Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('api.logout');

    // دعوات الموظفين والمديرين (فقط للأدمن)
    Route::middleware('isAdmin')->group(function () {
        Route::post('/invitations', [InvitationController::class, 'store'])->name('api.invitations.store');
    });

    // Accounts
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::post('/create', [AccountController::class, 'store']);
    Route::get('/account/{id}', [AccountController::class, 'show']);
    Route::put('/update-account/{id}', [AccountController::class, 'update']);
    Route::delete('/account/{id}', [AccountController::class, 'destroy']);
    Route::get('/account/total-balance/{id}', [AccountController::class, 'totalBalance']);

    // Transactions
    Route::post('/transactions/transfer', [TransactionController::class, 'transfer']);
    Route::get('/transactions/{accountId}/history', [TransactionController::class, 'history']);

    // Interest
    Route::get('/interest/{accountId}/calculate', [InterestController::class, 'calculate']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // Facade Summary
    Route::get('/banking/{accountId}/summary', [BankingController::class, 'summary']);
});

// قبول الدعوة عبر رابط الإيميل (لا يحتاج توكن مسبق)
Route::post('/invitations/accept/{token}', [InvitationController::class, 'accept'])->name('api.invitations.accept');
