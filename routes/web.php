<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ================= AUTHENTICATION ROUTES (GUEST) =================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// ================= AUTHENTICATED ROUTES =================
Route::middleware('auth')->group(function () {

    // ===== Auth =====
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/heartbeat', [AuthController::class, 'heartbeat'])->name('heartbeat');
    Route::get('/me', [AuthController::class, 'me'])->name('me');
    Route::get('/online-users', [AuthController::class, 'getOnlineUsers'])->name('online.users');

    // ===== Dashboard =====
    Route::get('/dashboard', [CustomerController::class, 'index'])->name('dashboard');
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    // ===== Profile Routes =====
    Route::prefix('profile')->group(function () {
        Route::post('/avatar', [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
        Route::get('/', [ProfileController::class, 'getProfile'])->name('profile.get');
        Route::put('/', [ProfileController::class, 'updateProfile'])->name('profile.update');
    });

    // ===== Notification Routes =====
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readall');
        Route::post('/delete-all', [NotificationController::class, 'deleteAll'])->name('notifications.deleteAll'); // 🔥 NEW ROUTE
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    });

    // ===== Customer Routes =====
    Route::prefix('customers')->group(function () {
        Route::get('/', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/search', [CustomerController::class, 'search'])->name('customers.search');
        Route::post('/', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/{unique_id}', [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('/{unique_id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        Route::post('/{unique_id}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
    });

    // ===== Receipts Routes =====
    Route::prefix('receipts')->group(function () {
        Route::get('/', [ReceiptController::class, 'index'])->name('receipts.index');
        Route::get('/search', [ReceiptController::class, 'search'])->name('receipts.search');
        Route::post('/', [ReceiptController::class, 'store'])->name('receipts.store');
        Route::get('/{id}', [ReceiptController::class, 'show'])->name('receipts.show');
        Route::put('/{id}', [ReceiptController::class, 'update'])->name('receipts.update');
        Route::delete('/{id}', [ReceiptController::class, 'destroy'])->name('receipts.destroy');
        Route::post('/{id}/restore', [ReceiptController::class, 'restore'])->name('receipts.restore');
        Route::get('/customer/{customerId}', [ReceiptController::class, 'getByCustomer'])->name('receipts.by_customer');
        Route::get('/statistics/all', [ReceiptController::class, 'statistics'])->name('receipts.statistics');
    });

    // ===== States for dropdown AJAX =====
    Route::get('/states', function () {
        $states = DB::table('states')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $states
        ]);
    })->name('states.list');
});