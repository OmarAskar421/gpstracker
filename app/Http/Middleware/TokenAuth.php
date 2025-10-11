<?php
// app/Http/Middleware/TokenAuth.php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication token required'
            ], 401);
        }

        $user = User::where('token', $token)
                    ->where('is_active', true)
                    ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token'
            ], 401);
        }

        // Now this will work because User extends Authenticatable
        auth()->setUser($user);

        return $next($request);
    }
}