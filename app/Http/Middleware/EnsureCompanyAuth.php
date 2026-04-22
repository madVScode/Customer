<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!session('company_auth')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Sesi syarikat telah tamat.'], 401);
            }
            return redirect()->route('company.login')
                             ->with('error', 'Sila log masuk terlebih dahulu.');
        }

        // Inject company info ke dalam request supaya controller boleh guna
        $request->merge(['__company_auth' => session('company_auth')]);

        return $next($request);
    }
}