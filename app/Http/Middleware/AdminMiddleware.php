<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak. Halaman ini khusus Pengurus/Admin PKK.'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Halaman ini hanya dapat diakses oleh Pengurus/Admin PKK.');
        }

        return $next($request);
    }
}
