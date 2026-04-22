<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // ✅ DEMO COMPANY CONSTANTS
    private const DEMO_COMPANY_ID = 'af6d921e-0e2b-490e-bf93-a4d3b36c3e610';
    private const DEMO_USER_ID = '6545f16a9-dd24-4fd9-851e-9f6376ecb9d5';
    
    // =========================================================
    // Show Login Page (Owner Business + Tester sahaja)
    // =========================================================
    public function showLogin()
{
    if (Auth::check()) {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->isTourist()) {
            return redirect()->route('tourist.dashboard');
        }
        return redirect()->route('dashboard');
    }
    return view('auth.login');
}

    // =========================================================
    // Show Admin Login Page (Admin System sahaja)
    // ✅ URL berasingan: /admin/login
    // =========================================================
    public function showAdminLogin()
{
    if (Auth::check()) {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if ($user->isTourist()) {
            return redirect()->route('tourist.dashboard');
        }
        return redirect()->route('dashboard');
    }
    return view('auth.admin-login');
}

    // =========================================================
    // Process Admin Login
    // ✅ Hanya benarkan role admin_system — tolak semua lain
    // ✅ Log setiap percubaan (berjaya atau gagal)
    // =========================================================
    public function adminLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        // ── Cuba authenticate ─────────────────────────────────
        if (!Auth::attempt($credentials, $remember)) {
            Log::warning('Admin login gagal', [
                'email'      => $request->email,
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Emel atau kata laluan tidak sah.',
            ], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        // ── Semak role — MESTI admin_system ──────────────────
        if ($user->role !== 'admin_system') {
            Auth::logout();
            $request->session()->invalidate();

            Log::warning('Akses admin ditolak — role tidak sah', [
                'email' => $request->email,
                'role'  => $user->role,
                'ip'    => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Halaman ini hanya untuk Admin Sistem.',
            ], 403);
        }

        // ── Login berjaya ─────────────────────────────────────
        $request->session()->regenerate();
        $user->updateOnlineStatus(true);

        $this->logActivity($user, 'admin_login', $request);

        // ✅ Notify semua staff bila admin_system login
        NotificationController::notifyAdminLoginLogout(
            adminUserId: $user->id,
            type: 'admin_login',
            title: 'Admin Online',
            message: "{$user->name} telah login ke sistem"
        );

        $this->notifyOtherAdmins($user, 'user_login', $user->name . ' (Admin) is now online');

        return response()->json([
            'success'  => true,
            'message'  => 'Selamat datang, ' . $user->name . '!',
            'redirect' => route('dashboard'),
            'user'     => $this->formatUser($user),
        ]);
    }

    // =========================================================
    // Process Login (Owner Business + Tester)
    // ✅ Standard email login + owner_business via registration_no
    // ✅ admin_system TIDAK dibenarkan melalui route ini
    // =========================================================
    public function login(Request $request)
    {
        // ── Owner Business login via No. Pendaftaran ──────────
        if ($request->input('login_type') === 'owner_business') {
            $request->validate([
                'registration_no' => 'required|string',
                'password'        => 'required|string',
            ]);

            $regNo   = trim($request->registration_no);
            $company = Company::where('registration_no', $regNo)->first();

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'No. pendaftaran syarikat tidak dijumpai. Pastikan no. pendaftaran betul.',
                ], 401);
            }

            $user = User::where(function ($q) use ($company) {
                    $q->where('company_id', $company->id)
                      ->orWhere('company_id', (string) $company->id);
                })
                ->where('role', 'owner_business')
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tiada akaun owner_business untuk syarikat ini. Sila hubungi admin.',
                ], 401);
            }

            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kata laluan tidak sah.',
                ], 401);
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();
            $user->updateOnlineStatus(true);

            $this->logActivity($user, 'login', $request);
            $this->notifyOtherAdmins($user, 'user_login', $user->name . ' is now online');

            return response()->json([
                'success'  => true,
                'message'  => 'Selamat datang, ' . $user->name . '!',
                'redirect' => route('dashboard'),
                'user'     => $this->formatUser($user),
            ]);
        }

        // ── Standard email + password login (Tester) ─────────
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            /** @var User $user */
            $user = Auth::user();

            // ✅ Halang admin_system dari guna route login biasa
            if ($user->role === 'admin_system') {
                Auth::logout();
                $request->session()->invalidate();

                return response()->json([
                    'success' => false,
                    'message' => 'Akaun admin mesti log masuk melalui halaman Admin Login.',
                ], 403);
            }
            if ($user->isTourist()) {
    Auth::logout();
    $request->session()->invalidate();

    return response()->json([
        'success' => false,
        'message' => 'Sila gunakan portal pelancong untuk log masuk.',
        'redirect' => route('tourist.login'),
    ], 403);
}

            $request->session()->regenerate();
            $user->updateOnlineStatus(true);

            $this->logActivity($user, 'login', $request);
            $this->notifyOtherAdmins($user, 'user_login', $user->name . ' is now online');

            // ✅ Untuk tester, ensure demo company data ready
            if ($user->role === 'tester') {
                $this->ensureDemoCompanyExists();
            }

            return response()->json([
                'success'  => true,
                'message'  => 'Login successful! Welcome back ' . $user->name,
                'redirect' => route('dashboard'),
                'user'     => $this->formatUser($user),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid email or password. Please try again.',
        ], 401);
    }

    // =========================================================
    // Logout
    // ✅ Admin dihalakan ke /admin/login, lain ke /login
    // ✅ Tester data cleanup menggunakan dynamic company_id
    // =========================================================
    public function logout(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $wasAdmin   = $user && $user->role === 'admin_system';
        $wasTourist = $user && $user->isTourist();

        if ($user) {
            $user->updateOnlineStatus(false);
            $this->logActivity($user, 'logout', $request);

            // ✅ admin_system logout → notifyAdminLoginLogout
            if ($user->role === 'admin_system') {
                NotificationController::notifyAdminLoginLogout(
                    adminUserId: $user->id,
                    type: 'admin_logout',
                    title: 'Admin Offline',
                    message: "{$user->name} telah logout dari sistem"
                );
            }

            // ✅ owner_business + admin_system lain dapat notification ini
            $this->notifyOtherAdmins($user, 'user_logout', $user->name . ' is now offline');

            // ── Tester cleanup ────────────────────────────────
            // ✅ FIXED: Guna company_id dari user, bukan hardcoded
            if ($user->role === 'tester' && $user->company_id) {
                $this->cleanupDemoData($user->company_id);
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // ✅ Admin kembali ke halaman admin login
        return $wasAdmin
    ? redirect()->route('admin.login')
    : ($wasTourist
        ? redirect()->route('tourist.login')
        : redirect()->route('login'));
            
    }

    // =========================================================
    // Check if user is authenticated
    // =========================================================
    public function check()
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            return response()->json([
                'authenticated' => true,
                'user'          => $this->formatUser($user, extended: true),
            ]);
        }

        return response()->json(['authenticated' => false], 401);
    }

    // =========================================================
    // Forgot Password
    // =========================================================
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'We couldn\'t find an account with that email address.',
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
        }

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        $token = Str::random(64);
        DB::table('password_reset_tokens')->insert([
            'email'      => $request->email,
            'token'      => Hash::make($token),
            'created_at' => now(),
        ]);

        $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($request->email));

        ActivityLog::create([
            'user_id'     => $user->id,
            'action'      => 'password_reset_requested',
            'model'       => 'User',
            'model_id'    => $user->id,
            'description' => 'Password reset requested for ' . $user->email,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        try {
            Mail::raw(
                "Hello!\n\nClick here to reset your password:\n" . $resetUrl .
                "\n\nThis link expires in 60 minutes.\n\nRegards,\nCustomer Pro",
                function ($message) use ($request) {
                    $message->to($request->email)->subject('Reset Password - Customer Pro');
                }
            );
        } catch (\Exception $e) {
            Log::error('Password reset email failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset link has been sent to your email address.',
        ]);
    }

    // =========================================================
    // Show Reset Form
    // =========================================================
    public function showResetForm(Request $request, $token)
    {
        $email = $request->query('email');
        if (!$token || !$email) {
            return redirect()->route('login')->with('error', 'Invalid password reset link.');
        }

        $resetRecord = DB::table('password_reset_tokens')->where('email', $email)->first();
        if (!$resetRecord) {
            return redirect()->route('login')->with('error', 'Invalid or expired password reset link.');
        }

        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return redirect()->route('login')->with('error', 'Password reset link has expired.');
        }

        return view('auth.reset-password', ['token' => $token, 'email' => $email]);
    }

    // =========================================================
    // Reset Password
    // =========================================================
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed',
        ], ['password.confirmed' => 'Passwords do not match.']);

        $resetRecord = DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$resetRecord) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired reset link.'], 400);
        }

        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['success' => false, 'message' => 'Reset link has expired.'], 400);
        }

        if (!Hash::check($request->token, $resetRecord->token)) {
            return response()->json(['success' => false, 'message' => 'Invalid reset token.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        ActivityLog::create([
            'user_id'     => $user->id,
            'action'      => 'password_reset_completed',
            'model'       => 'User',
            'model_id'    => $user->id,
            'description' => 'Password reset completed for ' . $user->email,
            'ip_address'  => $request->ip(),
            'user_agent'  => $request->userAgent(),
        ]);

        Notification::create([
            'user_id'      => $user->id,
            'triggered_by' => $user->id,
            'type'         => 'password_changed',
            'title'        => 'Password Changed',
            'message'      => 'Your password has been successfully changed.',
        ]);

        return response()->json([
            'success'  => true,
            'message'  => 'Password reset successfully! You can now login.',
            'redirect' => route('login'),
        ]);
    }

    // =========================================================
    // Heartbeat — keep session alive, update online users
    // ✅ Throttle DB write (30s), cache online_users (15s)
    // =========================================================
    public function heartbeat(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        $cacheKey = 'heartbeat_written_' . $user->id;
        if (!Cache::has($cacheKey)) {
            $user->updateOnlineStatus(true);
            Cache::put($cacheKey, true, now()->addSeconds(30));
        }

        $onlineUsers = Cache::remember('online_users_list', 15, function () {
            return $this->getOnlineUsers();
        });

        return response()->json([
            'success'      => true,
            'online_users' => $onlineUsers,
        ]);
    }

    // =========================================================
    // Get Online Users
    // =========================================================
    public function getOnlineUsers()
    {
        return User::where('is_online', true)
            ->where('id', '!=', Auth::id())
            ->select('id', 'name', 'email', 'role', 'avatar', 'last_seen')
            ->get()
            ->map(function (User $user) {
                return [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'role'      => $user->role,
                    'role_name' => $user->role_name,
                    'avatar'    => $user->avatar_url,
                    'last_seen' => $user->last_seen?->diffForHumans(),
                ];
            });
    }

    // =========================================================
    // Get Current User Info
    // =========================================================
    public function me()
    {
        if (!Auth::check()) {
            return response()->json(['success' => false], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'user'    => $this->formatUser($user, true),
        ]);
    }

    // =========================================================
    // Private Helpers
    // =========================================================

    private function formatUser(User $user, bool $extended = false): array
    {
        $base = [
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'role'       => $user->role,
            'role_name'  => $user->role_name,
            'avatar'     => $user->avatar_url,
            'company_id' => $user->company_id,
        ];

        if ($extended) {
            $base['is_admin_manage']      = method_exists($user, 'isAdminManage') ? $user->isAdminManage() : false;
            $base['is_admin_record']      = method_exists($user, 'isAdminRecord') ? $user->isAdminRecord() : false;
            $base['unread_notifications'] = method_exists($user, 'unreadNotificationsCount') ? $user->unreadNotificationsCount() : 0;
        }

        return $base;
    }

    private function logActivity(User $user, string $action, Request $request): void
    {
        try {
            ActivityLog::create([
                'user_id'     => $user->id,
                'action'      => $action,
                'model'       => 'User',
                'model_id'    => $user->id,
                'description' => $user->name . ' ' . $action,
                'ip_address'  => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);
        } catch (\Exception $e) {
            Log::error('ActivityLog failed: ' . $e->getMessage());
        }
    }

    private function notifyOtherAdmins(User $currentUser, string $type, string $message): void
    {
        try {
            // ✅ owner_business DAN admin_system dapat notification ini
            $otherUsers = User::whereIn('role', ['admin_system', 'owner_business'])
                ->where('id', '!=', $currentUser->id)
                ->get();

            foreach ($otherUsers as $user) {
                Notification::create([
                    'user_id'      => $user->id,
                    'triggered_by' => $currentUser->id,
                    'type'         => $type,
                    'title'        => 'User Status Update',
                    'message'      => $message,
                    'is_read'      => false,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Notification failed: ' . $e->getMessage());
        }
    }

    // =========================================================
    // ✅ NEW: Demo Company Management
    // =========================================================

    /**
     * Ensure demo company exists dengan data yang betul
     */
    private function ensureDemoCompanyExists(): void
    {
        try {
            $company = Company::find(self::DEMO_COMPANY_ID);
            
            if (!$company) {
                Company::create([
                    'id'              => self::DEMO_COMPANY_ID,
                    'agn'             => '0AGN-2026-000051',
                    'name'            => 'Demo Sdn. Bhd.',
                    'registration_no' => '03-0000076343852354 (57445-DEMO)',
                    'address'         => 'No.35 tkt 3 blok 45 flat taman cendana',
                ]);
                
                Log::info('Demo company created', ['company_id' => self::DEMO_COMPANY_ID]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to ensure demo company exists: ' . $e->getMessage());
        }
    }

    /**
     * Cleanup demo data bila tester logout
     * ✅ FIXED: Guna parameter company_id instead of hardcoded
     */
    private function cleanupDemoData(string $companyId): void
    {
        try {
            $deletedCustomers = DB::table('customers')
                ->where('company_id', $companyId)
                ->where('is_protected', false)
                ->delete();

            $deletedTrips = DB::table('trips')
                ->where('company_id', $companyId)
                ->where('is_protected', false)
                ->delete();

            $deletedBookings = DB::table('bookings')
                ->whereIn('trip_id', function ($q) use ($companyId) {
                    $q->select('id')
                      ->from('trips')
                      ->where('company_id', $companyId);
                })
                ->where('is_protected', false)
                ->delete();

            Log::info('Tester data cleanup completed', [
                'company_id'        => $companyId,
                'deleted_customers' => $deletedCustomers,
                'deleted_trips'     => $deletedTrips,
                'deleted_bookings'  => $deletedBookings,
            ]);
        } catch (\Exception $e) {
            Log::error('Tester cleanup failed: ' . $e->getMessage(), [
                'company_id' => $companyId,
            ]);
        }
    }
}