<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

/**
 * RoleMiddleware — Semak role pengguna sebelum akses route
 *
 * Cara guna dalam routes/web.php:
 *   ->middleware('role:admin_system')
 *   ->middleware('role:admin_system,owner_business,tester')
 *   ->middleware('role:tourist')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // ✅ FIX: Semak roles yang dibenarkan DULU
        // Sebelum ni, tourist redirect ke dashboard WALAUPUN route perlukan role:tourist
        if (!empty($roles) && in_array($user->role, $roles)) {
            // Role user ada dalam senarai dibenarkan — bagi laluan
            return $next($request);
        }

        // ✅ Tourist cuba akses admin panel (route bukan untuk tourist)
        if ($user->role === 'tourist') {
            return redirect()->route('tourist.dashboard')
                ->with('error', 'Anda tidak mempunyai akses ke panel admin.');
        }

        // Role tidak dibenarkan untuk route ini
        if (!empty($roles) && !in_array($user->role, $roles)) {
            Log::warning('Unauthorized role access attempt', [
                'user_id' => $user->id,
                'role'    => $user->role,
                'url'     => $request->url(),
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak mempunyai kebenaran untuk tindakan ini.',
                ], 403);
            }

            return redirect()->back()
                ->with('error', 'Anda tidak mempunyai kebenaran untuk akses ini.');
        }

        return $next($request);
    }
}