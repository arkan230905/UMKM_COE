<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // If user is not authenticated, redirect to login instead of 403
        if (! $user) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        // Handle null or invalid roles
        if (empty($user->role) || ! in_array($user->role, \App\Models\User::VALID_ROLES, true)) {
            Log::critical('User with invalid role attempted access', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'route' => $request->path(),
            ]);
            
            // Force logout and redirect to login
            auth()->logout();
            return redirect()->route('login')->with('error', 'Your account has an invalid configuration. Please contact support.');
        }

        // Check if user has required role
        if (! in_array($user->role, $roles, true)) {
            // Log access denial for security monitoring
            Log::warning('Access denied - insufficient role', [
                'user_id' => $user->id,
                'email' => $user->email,
                'user_role' => $user->role,
                'required_roles' => $roles,
                'route' => $request->path(),
            ]);

            // Pass context to 403 error page
            abort(403, '', [
                'required_roles' => $roles,
                'user_role' => $user->role,
                'user' => $user,
            ]);
        }

        return $next($request);
    }
}
