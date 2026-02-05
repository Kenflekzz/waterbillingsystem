<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UsersAuthController;
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\TotalSubscribersController;
use App\Http\Controllers\TotalUnpaidController;
use App\Http\Controllers\TotalPaidController;
use App\Http\Controllers\TotalDisconnectedController;
use App\Http\Controllers\HomepageController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\BehaviorController;
use App\Http\Controllers\UserBillingController;
use App\Http\Controllers\UserController;
use App\Models\Homepage;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\UserHomeController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ProblemReportController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\MyReportsController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\ConsumptionController;
use App\Http\Controllers\DisconnectPendingController;
use App\Http\Controllers\ConsumptionReportController;
// --------------------
// Home Route
// --------------------


Route::get('/', function () {
    $homepage = Homepage::first(); // get the first homepage row
    return view('welcome', compact('homepage'));
})->name('home');
Route::get('view-meternumber', function () {
    return view('view_MeterNumber');
})->name('ViewMeterNumber');

// Public page for reading water bill
Route::get('read-waterbill', function () {
    $homepage = Homepage::first() ?? new \stdClass();
    return view('read_WaterBill', compact('homepage'));
})->name('ReadWaterBill');
// --------------------
// User Routes
// --------------------


// --------------------
// User Routes
// --------------------
Route::prefix('user')->name('user.')->middleware('web')->group(function () {
    Route::get('/login', [UsersAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UsersAuthController::class, 'apiLogin']); // session persists
    Route::get('/register', [UsersAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [UsersAuthController::class, 'apiRegister']);
    Route::post('/password/email',   [UsersAuthController::class, 'sendResetOtp']);
    Route::post('/password/reset',   [UsersAuthController::class, 'resetWithOtp']);
    // inside the same prefix('user') group
        Route::get('/reset-password', function () {
            return view('user.userlogin'); // or whatever Blade view bootstraps your Vue SPA
        })->name('reset-password');


    Route::middleware(['auth:user'])->group(function () {
        Route::get('/consumption', [UsersAuthController::class, 'consumption'])->name('consumption');
        Route::post('/logout', [UsersAuthController::class, 'logout'])->name('logout');
        Route::get('/profile', [Usercontroller::class, 'profile'])->name('profile');
        Route::get('/billing', [UserController::class, 'billing'])->name('billing');
        Route::get('/billing/{id}/print', [UserController::class, 'printBill'])->name('billing.print');
        Route::post('/billing/{id}/pay', [UserController::class, 'payBill'])->name('billing.pay');
        //Route::get('/consumption', [UserController::class, 'consumption'])->name('consumption');
        Route::post('/billings/{id}/gcash', [UserBillingController::class,'payWithGCash'])->name('billing.gcash');
        Route::post('/paymongo/webhook', [UserBillingController::class, 'handleWebhook']);
        Route::get('/billing/{id}/success', [UserBillingController::class, 'paymentSuccess'])->name('billing.success');
        Route::get('/billing/{id}/failed', [UserBillingController::class, 'paymentFailed'])->name('billing.failed');
        Route::post('/billing/{id}/pay-arrears', [UserBillingController::class, 'payArrearsOnly'])->name('billing.pay.arrears');
        Route::get('/billing/{id}/arrears-success', [UserBillingController::class, 'arrearsSuccess'])->name('billing.arrears.success');
        Route::get('/billing/{id}/arrears-failed', [UserBillingController::class, 'arrearsFailed'])->name('billing.arrears.failed');
        Route::get('/home', [UserHomeController::class, 'index'])->name('home');
        Route::get('/profile', [UserProfileController::class, 'index'])->name('profile');
        Route::put('/profile/update', [UserProfileController::class, 'updateProfile'])->name('updateProfile');
        Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('updatePassword');
        Route::post('/billing/report-problem', [ProblemReportController::class, 'submit'])->name('report.problem');
        Route::get('/reports', [MyReportsController::class, 'getUserReports'])->name('reports.list');
        Route::get('/notifications', [UserNotificationController::class, 'getNotifications'])->name('notifications');
        Route::post('/notifications/{id}/read', [UserNotificationController::class, 'markAsRead'])->name('notifications.read');

        Route::get('/billing/{billing}/billing_print', [UserBillingController::class, 'print'])->name('billing.print');

        Route::get('/user_receipt/{payment}', [UserBillingController::class, 'generateReceipt'])->name('receipt.view');
        Route::get('/user_receipt/download/{payment}', [UserBillingController::class, 'downloadReceipt'])->name('receipt.download');
        Route::get('/my-consumption', [ConsumptionController::class, 'index'])->name('consumption');

    });
    
});
// --------------------
// API Routes for Vue login/register
// --------------------
/*
Route::middleware('web')->prefix('api/user')->group(function () {
    Route::post('/login', [UsersAuthController::class, 'apiLogin']); 
    Route::post('/logout', [UsersAuthController::class, 'logout']); 
    Route::post('/register', [UsersAuthController::class, 'apiRegister']);
});
*/

Route::post('/paymongo/webhook', [UserBillingController::class, 'handleWebhook']);





// --------------------
// Admin Routes
// --------------------
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/register', [AdminAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AdminAuthController::class, 'ApiRegister']); 
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('password/email',  [AdminAuthController::class, 'sendResetOtp']);
    Route::post('password/reset',  [AdminAuthController::class, 'resetWithOtp']);

    Route::get('/reset-password', function () {
            return view('admin.adminlogin'); 
        })->name('reset-password');


    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AdminAuthController::class, 'dashboard'])->middleware('auth:admin')->name('dashboard');
    
    Route::get('/billings', [AdminAuthController::class, 'billings'])->middleware('auth:admin')->name('billings');
    Route::get('/admins', [AdminAuthController::class, 'admins'])->middleware('auth:admin')->name('admins');
    Route::get('/reports', [ReportsController::class, 'index'])->middleware('auth:admin')->name('admin_reports');
    Route::get('/clients', [AdminAuthController::class, 'clients'])->middleware('auth:admin')->name('clients');

    Route::resource('clients', ClientController::class);
    Route::get('/billings/next-id', [BillingController::class, 'nextId'])->name('billings.next-id');

    Route::resource('billings', BillingController::class);
    Route::resource('payments', PaymentController::class);

    Route::get('/billings/{id}/print', [BillingController::class, 'print'])->name('billings.print');
    Route::get('/billings/{clientId}/arrears', [BillingController::class, 'getClientArrears']);
    Route::get('/billings/{clientId}/penalty', [BillingController::class, 'getPenalty']);

    Route::get('/totals/total_subscribers', [TotalSubscribersController::class, 'index'])->name('total_subscribers');
    Route::get('/totals/total_unpaid', [TotalUnpaidController::class, 'index'])->name('total_unpaid');
    Route::get('/unpaid-consumers', [TotalUnpaidController::class, 'unpaidConsumers'])->name('unpaid_consumers');
    Route::get('/unpaid-consumers/print', [TotalUnpaidController::class, 'printUnpaidConsumers'])->name('print_unpaid_consumers');
    Route::get('/totals/total_paid', [TotalPaidController::class, 'index'])->name('total_paid');
    Route::get('/total-paid', [TotalPaidController::class, 'totalPaid'])->name('filter_paid_consumers');
    Route::get('/total-paid/print', [TotalPaidController::class, 'printTotalPaid'])->name('print_paid_consumers');
    Route::get('/totals/total_disconnected', [TotalDisconnectedController::class, 'index'])->name('totalDisconnected');
    Route::get('/total-disconnected/filter', [TotalDisconnectedController::class, 'filter'])->name('filter_disconnected');
    Route::get('/total-disconnected/print', [TotalDisconnectedController::class, 'print'])->name('print_disconnected');

    Route::get('print_reports', [ReportsController::class, 'print'])->name('print_reports');
    Route::get('/print_clients/print', [ClientController::class, 'print'])->name('print_clients.print');

    Route::get('/homepage/edit', [HomepageController::class, 'edit'])->name('homepage.edit');
    Route::put('/homepage/update', [HomepageController::class, 'update'])->name('homepage.update');

    Route::get('/messages', [MessageController::class, 'index'])->name('messages');

    // General and personal messages
    Route::post('/messages/general', [MessageController::class, 'sendGeneral'])->name('messages.sendGeneral');
    Route::post('/messages/personal', [MessageController::class, 'sendPersonal'])->name('messages.sendPersonal');

    // Optional: redirect GET requests to avoid the 405 error
    Route::get('/messages/general', function() {
        return redirect()->route('admin.messages');
    });
    Route::get('/messages/personal', function() {
        return redirect()->route('admin.messages');
    });

    Route::get('/behavior', [BehaviorController::class, 'index'])->name('admin.behavior');
    Route::get('/behavior/data', [BehaviorController::class, 'data'])->name('admin.behavior.data');
    Route::post('/behavior/data', [BehaviorController::class, 'store'])->name('admin.behavior.store');

    Route::get('/activity_log', [ActivityLogController::class, 'activityLog'])->name('activity.log');

    Route::get('/billings/{clientId}/previous-current', [BillingController::class, 'getPreviousCurrentBill']);



    Route::get('/user-reports', [AdminReportController::class, 'index'])
        ->name('reports');

    Route::post('/reports/update/{id}', [AdminReportController::class, 'updateStatus'])
        ->name('reports.update');
    
    Route::post('/set-demo-consumer', [BehaviorController::class, 'setDemoConsumer']);

    // routes/web.php  (or api.php if you prefer)
    Route::post('/set-demo-consumer/{id}', function ($id) {
        session(['demo_consumer' => $id]);
        return response()->noContent();
    });

    Route::get('/disconnect-pending', [DisconnectPendingController::class, 'index'])
     ->name('disconnect.pending');

    Route::post('disconnect/mark', [DisconnectPendingController::class, 'mark'])
     ->name('disconnect.mark');

    Route::get('/consumption_report', [ConsumptionReportController::class, 'index'])->name('consumption-report');



});

// --------------------
// API Routes       
// --------------------
//Route::prefix('api/admin')->group(function () {
   //Route::post('/login', [AdminAuthController::class, 'apiLogin']);
   //Route::post('/register', [AdminAuthController::class, 'apiRegister']);
//});


//Route::get('/{any}', function () {
        //return view('user.userregister');
    //})->where('any', '.*');


