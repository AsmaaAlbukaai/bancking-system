<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\InterestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\BankingController;

Route::middleware('auth:sanctum')->group(function () {

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
