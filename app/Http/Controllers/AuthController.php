<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * =========================================================
     * Show Login Page
     * =========================================================
     * - Jika user dah login → redirect ke dashboard
     * - Jika belum → paparkan login page
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * =========================================================
     * Process Login
     * =========================================================
     * - Validate input
     * - Attempt login
     * - Update online status
     * - Log activity
     * - Notify other users
     */
    public function login(Request $request)
    {
        // Validate login form
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Prepare credentials
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        // Attempt authentication
        if (Auth::attempt($credentials, $remember)) {
            // Regenerate session for security
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            // Mark user as online
            $user->updateOnlineStatus(true);

            // Log login activity
            ActivityLog::create([
                'user_id' => $user->id,
                'action' => 'login',
                'model' => 'User',
                'model_id' => $user->id,
                'description' => $user->name . ' logged in',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Notify other users about login
            $this->notifyOtherAdmins(
                $user,
                'user_login',
                $user->name . ' is now online'
            );

            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Login successful! Welcome back ' . $user->name,
                'redirect' => route('dashboard'),
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_name' => $user->role_name,
                    'avatar' => $user->avatar_url,
                ]
            ]);
        }

        // Login failed
        return response()->json([
            'success' => false,
            'message' => 'Invalid email or password. Please try again.',
        ], 401);
    }

    /**
     * =========================================================
     * Logout
     * =========================================================
     * - Update online status
     * - Log logout activity
     * - Notify other users
     * - Destroy session
     */
    public function logout(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        // Mark user as offline
        $user->updateOnlineStatus(false);

        // Log logout activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'logout',
            'model' => 'User',
            'model_id' => $user->id,
            'description' => $user->name . ' logged out',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Notify other users
        $this->notifyOtherAdmins(
            $user,
            'user_logout',
            $user->name . ' is now offline'
        );

        // Logout & destroy session
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * =========================================================
     * Heartbeat (Keep user online)
     * =========================================================
     * - Dipanggil secara berkala (AJAX)
     * - Update last_seen
     * - Return list of online users
     */
    public function heartbeat(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        // Refresh online status
        $user->updateOnlineStatus(true);

        return response()->json([
            'success' => true,
            'online_users' => $this->getOnlineUsers(),
        ]);
    }

    /**
     * =========================================================
     * Get Online Users
     * =========================================================
     * - Exclude current user
     * - Return formatted user list
     */
    public function getOnlineUsers()
    {
        return User::where('is_online', true)
            ->where('id', '!=', Auth::id())
            ->select('id', 'name', 'email', 'role', 'avatar', 'last_seen')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'role_name' => $user->role_name,
                    'avatar' => $user->avatar_url,
                    'last_seen' => $user->last_seen?->diffForHumans(),
                ];
            });
    }

    /**
     * =========================================================
     * Notify Other Users
     * =========================================================
     * - Create notification for all users except current
     */
    private function notifyOtherAdmins(User $currentUser, string $type, string $message): void
    {
        $otherUsers = User::where('id', '!=', $currentUser->id)->get();

        foreach ($otherUsers as $user) {
            Notification::create([
                'user_id' => $user->id,
                'triggered_by' => $currentUser->id,
                'type' => $type,
                'title' => 'User Status Update',
                'message' => $message,
            ]);
        }
    }

    /**
     * =========================================================
     * Get Current Logged In User Info
     * =========================================================
     * - Used by frontend (profile, navbar, etc)
     */
    public function me()
    {
        if (!Auth::check()) {
            return response()->json(['success' => false], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'role_name' => $user->role_name,
                'avatar' => $user->avatar_url,
                'is_admin_manage' => $user->isAdminManage(),
                'is_admin_record' => $user->isAdminRecord(),
                'unread_notifications' => $user->unreadNotificationsCount(),
            ]
        ]);
    }
}
