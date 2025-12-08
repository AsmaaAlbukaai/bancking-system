<?php

use App\Modules\Account\AccountController;
use App\Modules\Auth\InvitationController;
use App\Modules\Auth\LoginController;
use App\Modules\Auth\RegisterController;
use App\Modules\Auth\VerifyCodeController;
use App\Modules\Banking\BankingController;
use App\Modules\Interest\InterestController;
use App\Modules\Notification\NotificationController;
use App\Modules\Transaction\TransactionController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

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
    Route::post('/accounts', [AccountController::class, 'store']);
    Route::get('/accounts/{id}', [AccountController::class, 'show']);
    Route::put('/accounts/{id}', [AccountController::class, 'update']);
    Route::delete('/accounts/{id}', [AccountController::class, 'destroy']);
    Route::get('/accounts/{id}/total-balance', [AccountController::class, 'totalBalance']);

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
Route::get('/test', [AccountController::class, 'testSwagger']);


