<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TesterModeMiddleware
 *
 * Tester kini mempunyai akses penuh seperti owner —
 * semua operasi (baca & tulis) dibenarkan ke database.
 */
class TesterModeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}