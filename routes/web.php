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

// --------------------
// Home Route
// --------------------
Route::get('/', function () {
    return view('welcome');
})->name('home');

// --------------------
// User Routes
// --------------------
Route::prefix('user')->name('user.')->group(function () {
    Route::get('/login', [UsersAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [UsersAuthController::class, 'login']);

    Route::get('/register', [UsersAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [UsersAuthController::class, 'apiRegister']);

   Route::middleware(['auth:user'])->group(function () {
    Route::get('/dashboard', [UsersAuthController::class, 'dashboard'])->name('dashboard');
});

    Route::post('/logout', [UsersAuthController::class, 'logout'])->name('logout');

    // SPA fallback route — keep it LAST so it doesn't override login/register
    Route::get('/{any}', function () {
        return view('user.userregister');
    })->where('any', '.*');
});

// --------------------
// Admin Routes
// --------------------
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/register', [AdminAuthController::class, 'showRegisterForm'])->name('register');
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [AdminAuthController::class, 'dashboard'])->middleware('auth:admin')->name('dashboard');
    
    Route::get('/billings', [AdminAuthController::class, 'billings'])->middleware('auth:admin')->name('billings');
    Route::get('/admins', [AdminAuthController::class, 'admins'])->middleware('auth:admin')->name('admins');
    Route::get('/reports', [ReportsController::class, 'index'])->middleware('auth:admin')->name('reports');
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
    Route::get('/totals/total_paid', [TotalPaidController::class, 'index'])->name('total_paid');
    Route::get('/totals/total_disconnected', [TotalDisconnectedController::class, 'index'])->name('totalDisconnected');

    Route::get('print_reports', [ReportsController::class, 'print'])->name('print_reports');
    Route::get('/print_clients/print', [ClientController::class, 'print'])->name('print_clients.print');
});

// --------------------
// API Routes
// --------------------
Route::prefix('api/admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'apiLogin']);
    Route::post('/register', [AdminAuthController::class, 'apiRegister']);
});

Route::prefix('api/user')->group(function () {
    Route::post('/login', [UsersAuthController::class, 'login']); // changed from apiLogin
    Route::post('/logout', [UsersAuthController::class, 'logout']); // changed from apiLogout
    Route::post('/register', [UsersAuthController::class, 'apiRegister']);
});
