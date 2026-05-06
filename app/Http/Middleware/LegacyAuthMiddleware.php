<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LegacyAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if user is already authenticated
        if (Auth::check()) {
            return $next($request);
        }

        // 2. Start session if not started (for legacy)
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }

        // 3. Try legacy session
        if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
            $user = User::find($_SESSION['user']['id']);
            if ($user) {
                Auth::login($user);
                return $next($request);
            }
        }

        // 4. Try Sanctum guard (for Postman/Tokens)
        try {
            $user = Auth::guard('sanctum')->user();
            if ($user) {
                Auth::login($user);
                return $next($request);
            }
        } catch (\Exception $e) {
            // Ignore sanctum errors here
        }

        return $next($request);
    }
}
