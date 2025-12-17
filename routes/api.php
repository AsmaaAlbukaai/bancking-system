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
use App\Modules\Account\AccountStatusController;
use App\Modules\Recommendations\RecommendationController;
use App\Modules\Reports\ReportsController;
use App\Modules\Support\SupportController;
use App\Modules\Transaction\Recurring\RecurringController;
use App\Modules\User\UserController;

// Auth: register + login (بدون توكن مسبق)
Route::post('register', [RegisterController::class, 'register'])->name('api.register');
Route::post('/login', [LoginController::class, 'login'])->name('api.login');
Route::post('/email/verify-code', [VerifyCodeController::class, 'verify'])->name('api.verify-code');
Route::post('/email/resend-code', [VerifyCodeController::class, 'resend'])->name('api.resend-code');


Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('api.logout');

    Route::prefix('accounts')->group(function () {

    // تقديم طلب تغيير حالة الحساب
    Route::post('request-status-change/{accountId}', [AccountStatusController::class, 'requestChange']);

    // إلغاء
    Route::post('cancel/{id}', [AccountStatusController::class, 'cancel']);

    // جميع طلبات الزبون
    Route::get('my-status-change-requests', [AccountStatusController::class, 'myRequests']);

    // الطلبات حسب حساب معين
    Route::get('status-change-requests/{accountId}', [AccountStatusController::class, 'accountRequests']);
    });

    // دعوات الموظفين والمديرين (فقط للأدمن)
    Route::middleware('isAdmin')->group(function () {
    Route::post('/invitations', [InvitationController::class, 'store'])->name('api.invitations.store');
    Route::get('/transactions/all', [TransactionController::class, 'allTransactions']);
        // إغلاق/تفعيل حساب
    Route::post('/accounts/deactivate/{id}', [AccountController::class, 'deactivateAccount']);
    Route::post('/accounts/activate/{id}',   [AccountController::class, 'activateAccount']);

    Route::get('/financial', [ReportsController::class, 'financialReport']);
    // حذف موظف
    Route::delete('/employees/{id}', [UserController::class, 'deleteEmployee']);
    });

    // Accounts
    Route::get('/accounts', [AccountController::class, 'index']);
    Route::post('/create', [AccountController::class, 'store']);
    Route::get('/account/{id}', [AccountController::class, 'show']);
    Route::put('/update-account/{id}', [AccountController::class, 'update']);
    Route::delete('/account/{id}', [AccountController::class, 'destroy']);
    Route::get('/account/total-balance/{id}', [AccountController::class, 'totalBalance']);



    // العميل يشاهد ملفه الشخصي
    Route::get('/user/profile', [UserController::class, 'myProfile']);

    // (admin, manager, teller) مشاهدة العملاء
    Route::get('/customers', [UserController::class, 'getAllCustomers']);
    Route::get('/employees', [UserController::class, 'getAllEmployees']);
    Route::get('/tellers', [UserController::class, 'getAllTellers']);
    // Transactions
    Route::post('/transactions/transfer', [TransactionController::class, 'transfer']);

    Route::get('/transactions/history/{accountId}', [TransactionController::class, 'history']);

    // Interest
    Route::get('/interest/{accountId}/calculate', [InterestController::class, 'calculate']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread', [NotificationController::class, 'unread']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);

    // Payment Gateways
    Route::get('/payment-gateways', [\App\Modules\Payment\PaymentController::class, 'listGateways']);
    Route::post('/accounts/{accountId}/deposit-gateway/{gatewayId}', [\App\Modules\Payment\PaymentController::class, 'depositViaGateway']);

    // Facade Summary
    Route::get('/banking/{accountId}/summary', [BankingController::class, 'summary']);
});



// طلبات الحالة
    Route::prefix('status-change-requests')->group(function () {

    Route::get('/', [AccountStatusController::class, 'index']);       // كل الطلبات
    Route::get('{id}', [AccountStatusController::class, 'show']);     // طلب واحد

    Route::middleware('auth:sanctum')->group(function () {
    Route::post('approve/{id}', [AccountStatusController::class, 'approve']);  // موافقة
    Route::post('reject/{id}', [AccountStatusController::class, 'reject']);    // رفض
});
    });
// قبول الدعوة عبر رابط الإيميل (لا يحتاج توكن مسبق)
Route::post('/invitations/accept/{token}', [InvitationController::class, 'accept'])->name('api.invitations.accept');
Route::get('/test', [AccountController::class, 'testSwagger']);

Route::middleware('auth:sanctum')->group(function () {

    // سحب / إيداع للزبون
    Route::post('transaction/{accountId}', [TransactionController::class, 'customerTransaction']);

    // موافقة teller
    Route::post('transactions/approve/{transactionId}', [TransactionController::class, 'approveCustomerTransaction']);

    // رفض teller
    Route::post('transactions/reject/{transactionId}', [TransactionController::class, 'rejectCustomerTransaction']);

    Route::post('/transactions/approve/manager/{id}',  [TransactionController::class, 'approveByManager']);

    Route::post('transactions/reject/manager/{id}', [TransactionController::class, 'rejectByManager']);

    Route::get('customer-transactions/requests', [TransactionController::class, 'customerRequests']);

    Route::get('customers/accounts/{userId}', [AccountController::class, 'getCustomerAccountsByUserId']);
     
    
    Route::post('request', [RecurringController::class, 'requestRecurring']); // customer
    Route::get('requests',  [RecurringController::class, 'listRequests']);   // teller/manager/admin
    Route::post('/approve/{id}', [RecurringController::class, 'approve']);    // teller
    Route::post('/reject/{id}', [RecurringController::class, 'reject']);      // teller
    
    
    Route::post('ticket', [SupportController::class, 'createTicket']);

    Route::post('/ticket/reply/{id}', [SupportController::class, 'reply']);

    Route::post('/ticket/close/{id}', [SupportController::class, 'closeTicket']);
      // المدير يشوف الشكاوي
    Route::get('/manager/complaints', [SupportController::class, 'managerComplaints']);

    // الصراف يشوف الاستفسارات
    Route::get('/teller/inquiries', [SupportController::class, 'tellerInquiries']);

    // المستخدم يشوف تذاكره الخاصة
    Route::get('/my-tickets', [SupportController::class, 'userTickets']);

    Route::get('/recommendations', [RecommendationController::class, 'getMyRecommendations']);
    Route::get('/staff', [ReportsController::class, 'staffReport']);
});

