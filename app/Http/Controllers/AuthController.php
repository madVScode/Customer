<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    /**
     * =========================================================
     * Show Login Page
     * =========================================================
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
     * 🔥 FORGOT PASSWORD - Send Reset Link
     * =========================================================
     */
    public function forgotPassword(Request $request)
    {
        // Validate email
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'We couldn\'t find an account with that email address.',
        ]);

        // Check if user exists
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'We couldn\'t find an account with that email address.',
            ], 404);
        }

        // Delete old tokens for this email
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Generate reset token
        $token = Str::random(64);

        // Store token in database
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

        // Create reset URL
        $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($request->email));

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'password_reset_requested',
            'model' => 'User',
            'model_id' => $user->id,
            'description' => 'Password reset requested for ' . $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // TODO: Send email with reset link
        // In production, you would send an email here:
        // Mail::to($request->email)->send(new ResetPasswordMail($resetUrl));

        // For now, return success with the URL (in production, don't return the URL!)
        return response()->json([
            'success' => true,
            'message' => 'Password reset link has been sent to your email address.',
            'reset_url' => $resetUrl, // ⚠️ REMOVE THIS IN PRODUCTION!
        ]);
    }

    /**
     * =========================================================
     * 🔥 SHOW RESET PASSWORD FORM
     * =========================================================
     */
    public function showResetForm(Request $request, $token)
    {
        $email = $request->query('email');

        // Validate that we have both token and email
        if (!$token || !$email) {
            return redirect()->route('login')
                ->with('error', 'Invalid password reset link.');
        }

        // Check if token exists and is valid (not expired)
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->first();

        if (!$resetRecord) {
            return redirect()->route('login')
                ->with('error', 'Invalid or expired password reset link.');
        }

        // Check if token is expired (60 minutes)
        $tokenAge = now()->diffInMinutes($resetRecord->created_at);
        if ($tokenAge > 60) {
            // Delete expired token
            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->delete();

            return redirect()->route('login')
                ->with('error', 'Password reset link has expired. Please request a new one.');
        }

        // Show reset password form
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * =========================================================
     * 🔥 PROCESS RESET PASSWORD
     * =========================================================
     */
    public function resetPassword(Request $request)
    {
        // Validate input
        $request->validate([
            'token' => 'required',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:6|confirmed',
        ], [
            'password.confirmed' => 'Passwords do not match.',
        ]);

        // Find reset token record
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$resetRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired password reset link.',
            ], 400);
        }

        // Check if token is expired (60 minutes)
        $tokenAge = now()->diffInMinutes($resetRecord->created_at);
        if ($tokenAge > 60) {
            // Delete expired token
            DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->delete();

            return response()->json([
                'success' => false,
                'message' => 'Password reset link has expired. Please request a new one.',
            ], 400);
        }

        // Verify token matches (compare with hashed token)
        if (!Hash::check($request->token, $resetRecord->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid password reset token.',
            ], 400);
        }

        // Find user
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        }

        // Update password
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete used token
        DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->delete();

        // Log activity
        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'password_reset_completed',
            'model' => 'User',
            'model_id' => $user->id,
            'description' => 'Password reset completed for ' . $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Notify user (optional)
        Notification::create([
            'user_id' => $user->id,
            'triggered_by' => $user->id,
            'type' => 'password_changed',
            'title' => 'Password Changed',
            'message' => 'Your password has been successfully changed.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password has been reset successfully! You can now login with your new password.',
            'redirect' => route('login'),
        ]);
    }

    /**
     * =========================================================
     * Heartbeat (Keep user online)
     * =========================================================
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