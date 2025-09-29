<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import Auth Facade
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->role === 'admin') {
            // Jika benar admin, lanjutkan request ke controller
            return $next($request);
        }

        // Jika bukan admin, kirim respons error 403 Forbidden
        return response()->json([
            'status' => 'error',
            'message' => 'Akses ditolak. Rute ini khusus untuk admin.'
        ], 403);

    }
}
