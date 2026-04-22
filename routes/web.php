<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\DashboardController;
use App\Models\State;
use App\Http\Controllers\Tourist\TouristController;

// ============================================================
// GUEST — Login utama: Owner Business + Tester sahaja
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// ============================================================
// GUEST — Admin System: halaman login berasingan
// ============================================================
Route::middleware('guest')->group(function () {
    Route::get('/admin/login',  [AuthController::class, 'showAdminLogin'])->name('admin.login');
    Route::post('/admin/login', [AuthController::class, 'adminLogin'])->name('admin.login.post');
});

// ============================================================
// FORGOT / RESET PASSWORD
// ============================================================
Route::post('/forgot-password',       [AuthController::class, 'forgotPassword'])->name('password.forgot');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password',        [AuthController::class, 'resetPassword'])->name('password.update');

// ============================================================
// TOURIST — PUBLIC (tanpa auth)
// ============================================================
Route::prefix('tourist')->name('tourist.')->group(function () {

    // Public pages
    Route::get('/',          [TouristController::class, 'index'])->name('index');
    Route::get('/trip/{id}', [TouristController::class, 'tripDetail'])->name('trip.detail');
     Route::post('/visitor/ping', [TouristController::class, 'visitorPing'])->name('visitor.ping');

    // Auth pages
    Route::get('/register',  [TouristController::class, 'showRegister'])->name('register');
    Route::post('/register', [TouristController::class, 'register'])->name('register.submit');
    Route::get('/login',     [TouristController::class, 'showLogin'])->name('login');
    Route::post('/login',    [TouristController::class, 'login'])->name('login.submit');
    Route::post('/logout',   [TouristController::class, 'logout'])->name('logout');

    // Public API — comments & likes (GET/POST tanpa auth)
    Route::get('/trip/{tripId}/comments', [TouristController::class, 'getComments'])->name('trip.comments');
    Route::post('/likes',                 [TouristController::class, 'getLikes'])->name('likes.get');

    // ── TOURIST AUTHENTICATED ─────────────────────────────────
    Route::middleware(['auth', 'role:tourist'])->group(function () {

        // Dashboard
        Route::get('/dashboard', [TouristController::class, 'dashboard'])->name('dashboard');

        // ── Booking flow ──────────────────────────────────────
        // ⚠️ Static routes MESTI sebelum /{id} routes
        Route::get('/booking/{tripId}',        [TouristController::class, 'showBooking'])->name('booking.show');
        Route::post('/booking',                [TouristController::class, 'createBooking'])->name('booking.create');
        Route::get('/booking/{id}/success',    [TouristController::class, 'bookingSuccess'])->name('booking.success');
        Route::post('/booking/{id}/travelers', [TouristController::class, 'saveTravelerDetails'])->name('booking.travelers');
        Route::get('/booking/{id}/pdf',        [TouristController::class, 'bookingPdf'])->name('booking.pdf');

        // My bookings list
        Route::get('/my-bookings', [TouristController::class, 'myBookings'])->name('my-bookings');

        // ── Social — like & comment ───────────────────────────
        Route::post('/trip/{tripId}/like',    [TouristController::class, 'toggleLike'])->name('trip.like');
        Route::post('/trip/{tripId}/comment', [TouristController::class, 'postComment'])->name('trip.comment');
        Route::delete('/comment/{commentId}', [TouristController::class, 'deleteComment'])->name('comment.delete');
    });
});

// ============================================================
// AUTHENTICATED — Admin / Owner / Tester
// ============================================================
Route::middleware('auth')->group(function () {

    Route::post('/logout',      [AuthController::class, 'logout'])->name('logout');
    Route::post('/heartbeat',   [AuthController::class, 'heartbeat'])->name('heartbeat');
    Route::get('/me',           [AuthController::class, 'me'])->name('me');
    Route::get('/online-users', [AuthController::class, 'getOnlineUsers'])->name('online.users');

    // Redirect root — tourist pergi ke tourist dashboard, lain pergi admin
    Route::get('/', function () {
    /** @var \App\Models\User|null $user */    
    $user = Auth::user();
    if ($user && $user->isTourist()) {
        return redirect()->route('tourist.dashboard');
    }
    return redirect('/dashboard');
});

    // ── States dropdown (API) ─────────────────────────────────
    Route::get('/states', function () {
        return response()->json([
            'success' => true,
            'data'    => State::select('id', 'name')->orderBy('name')->get(),
        ]);
    })->name('states.list');

    // ── Profile ───────────────────────────────────────────────
    Route::prefix('profile')->group(function () {
        Route::get('/',          [ProfileController::class, 'getProfile'])->name('profile.get');
        Route::put('/',          [ProfileController::class, 'updateProfile'])->name('profile.update');
        Route::post('/avatar',   [ProfileController::class, 'uploadAvatar'])->name('profile.avatar');
        Route::delete('/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.avatar.delete');
    });

    // ── Notifications ─────────────────────────────────────────
    Route::prefix('notifications')->group(function () {
        Route::get('/',             [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread');
        Route::post('/read-all',    [NotificationController::class, 'markAllAsRead'])->name('notifications.readall');
        Route::post('/delete-all',  [NotificationController::class, 'deleteAll'])->name('notifications.deleteAll');
        Route::post('/{id}/read',   [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::delete('/{id}',      [NotificationController::class, 'destroy'])->name('notifications.destroy');
    });

    // ── Dashboard Blade Views — ADMIN SAHAJA ──────────────────
    Route::middleware(['role:admin_system,owner_business,tester'])->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::prefix('dashboard')->name('dashboard.')->group(function () {
            Route::get('/add-customer',      [DashboardController::class, 'addCustomer'])->name('add-customer');
            Route::get('/companies',         [DashboardController::class, 'companies'])->name('companies');
            Route::get('/trips',             [DashboardController::class, 'trips'])->name('view-trips');
            Route::get('/trips/add',         [DashboardController::class, 'addTrip'])->name('add-trip');
            Route::get('/trips/{id}/edit',   [DashboardController::class, 'editTrip'])->name('trips.edit-page');
            Route::get('/trips/{id}/manage', [DashboardController::class, 'manageTrip'])->name('trips.manage-page');
            Route::get('/receipts',          [DashboardController::class, 'receipts'])->name('receipts');
            Route::get('/receipts/create',   [DashboardController::class, 'createReceipt'])->name('receipts.create');
            Route::get('/bookings',          [DashboardController::class, 'bookings'])->name('bookings');
            Route::get('/bookings/create',   [DashboardController::class, 'createBooking'])->name('bookings.create');
            Route::get('/settings',          [DashboardController::class, 'settings'])->name('settings');
        });
    });

    // ── API — admin_system, owner_business, tester ────────────
    Route::middleware(['role:admin_system,owner_business,tester'])->group(function () {

        // ── Customers ─────────────────────────────────────────
        // ⚠️ /api/customers/* MESTI sebelum prefix('customers')
        Route::get('/api/customers/by-company', [CustomerController::class, 'customersByCompany']);
        Route::get('/customers/count', [CustomerController::class, 'count']);

        Route::prefix('customers')->group(function () {
    // Static GET — dahulu
    Route::get('/search',     [CustomerController::class, 'search'])->name('customers.search');
    Route::get('/no-booking', [CustomerController::class, 'noBooking'])->name('customers.no-booking'); // ← TAMBAH DI SINI
    Route::get('/',           [CustomerController::class, 'index'])->name('customers.index');

    // Dynamic GET
    Route::get('/{unique_id}', [CustomerController::class, 'show'])->name('customers.show');

            // Write operations — tester intercepted
            Route::middleware('tester.intercept')->group(function () {
                Route::post('/',                    [CustomerController::class, 'store'])->name('customers.store');
                Route::put('/{unique_id}',          [CustomerController::class, 'update'])->name('customers.update');
                Route::post('/{unique_id}/restore', [CustomerController::class, 'restore'])->name('customers.restore');
                Route::delete('/{unique_id}',       [CustomerController::class, 'destroy'])->name('customers.destroy');
            });
        });

        // ── Companies ─────────────────────────────────────────
        Route::prefix('companies')->group(function () {
            // Static GET — dahulu
            Route::get('/search', [CompanyController::class, 'search'])->name('companies.search');
            Route::get('/',       [CompanyController::class, 'index'])->name('companies.index');

            // Admin only write operations
            Route::middleware(['role:admin_system', 'tester.intercept'])->group(function () {
                Route::post('/',                         [CompanyController::class, 'store'])->name('companies.store');
                Route::put('/{id}',                      [CompanyController::class, 'update'])->name('companies.update');
                Route::post('/{id}/set-portal-password', [CompanyController::class, 'setPortalPassword'])->name('companies.set-portal-password');
                Route::post('/{id}/toggle-portal',       [CompanyController::class, 'togglePortal'])->name('companies.toggle-portal');
                Route::delete('/{id}',                   [CompanyController::class, 'destroy'])->name('companies.destroy');
            });
        });

        // ── Trips ─────────────────────────────────────────────
        Route::prefix('trips')->group(function () {
            // ⚠️ /{id}/bookings MESTI sebelum /{id} supaya tidak conflict
            Route::get('/{id}/bookings', [TripController::class, 'tripBookings'])->name('trips.bookings');
            Route::get('/',              [TripController::class, 'index'])->name('trips.index');
            Route::get('/{id}',          [TripController::class, 'show'])->name('trips.show');

            Route::middleware('tester.intercept')->group(function () {
                Route::post('/',                            [TripController::class, 'store'])->name('trips.store');
                Route::post('/{id}',                        [TripController::class, 'update'])->name('trips.update');
                Route::delete('/{id}/bookings/{bookingId}', [TripController::class, 'removeTripBooking'])->name('trips.bookings.remove');
                Route::delete('/{id}',                      [TripController::class, 'destroy'])->name('trips.destroy');
            });
        });

        // ── Bookings ──────────────────────────────────────────
        // ⚠️ PENTING: semua static routes MESTI sebelum /{id} routes
        Route::prefix('bookings')->group(function () {

            // Static GET
            Route::get('/dash-stats', [BookingController::class, 'dashStats'])->name('bookings.stats');
            Route::get('/search',     [BookingController::class, 'search'])->name('bookings.search');
            Route::get('/',           [BookingController::class, 'index'])->name('bookings.index');

            // ✅ /{id}/travelers — MESTI sebelum /{id} generic routes
            // Tiada tester.intercept — admin & owner boleh update travelers terus
            Route::post('/{id}/travelers', [BookingController::class, 'updateTravelers'])->name('bookings.travelers');

            // ✅ Approve & Reject — tiada tester.intercept (admin action)
            Route::post('/{id}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
            Route::post('/{id}/reject',  [BookingController::class, 'reject'])->name('bookings.reject');

            // Write operations — tester intercepted
            Route::middleware('tester.intercept')->group(function () {
                Route::post('/',       [BookingController::class, 'store'])->name('bookings.store');
                Route::put('/{id}',    [BookingController::class, 'update'])->name('bookings.update');
                Route::delete('/{id}', [BookingController::class, 'destroy'])->name('bookings.destroy');
            });
        });

        // ── Receipts ──────────────────────────────────────────
        Route::prefix('receipts')->group(function () {
            // ⚠️ Static routes dahulu
            Route::get('/customers-with-bookings', [ReceiptController::class, 'customersWithBookings'])->name('receipts.customers_with_bookings');
            Route::get('/search',                  [ReceiptController::class, 'search'])->name('receipts.search');
            Route::get('/statistics/all',          [ReceiptController::class, 'statistics'])->name('receipts.statistics');
            Route::get('/customer/{customerId}',   [ReceiptController::class, 'getByCustomer'])->name('receipts.by_customer');
            Route::get('/',                        [ReceiptController::class, 'index'])->name('receipts.index');
            Route::get('/{id}',                    [ReceiptController::class, 'show'])->name('receipts.show');

            Route::middleware('tester.intercept')->group(function () {
                Route::post('/',             [ReceiptController::class, 'store'])->name('receipts.store');
                Route::put('/{id}',          [ReceiptController::class, 'update'])->name('receipts.update');
                Route::post('/{id}/restore', [ReceiptController::class, 'restore'])->name('receipts.restore');
                Route::delete('/{id}',       [ReceiptController::class, 'destroy'])->name('receipts.destroy');
            });
        });

        // ── Admin System only ──────────────────────────────────
        Route::middleware(['role:admin_system'])->group(function () {
            Route::get('/admin/users', fn() => response()->json([]))->name('admin.users');
        });

    }); // end role middleware
});