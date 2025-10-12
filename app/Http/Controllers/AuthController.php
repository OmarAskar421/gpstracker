<?php
// app/Http/Controllers/AuthController.php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\CarResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'secret_code' => 'required|string|max:20'
        ]);

        // Find user by secret code
        $user = User::where('secret_code', $request->secret_code)
                    ->where('is_active', true)
                    ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid secret code or user inactive'
            ], 401);
        }

        // Generate JWT-like token (simple version)
        $token = Str::random(60);
        
        // Store token in database
        $user->update(['token' => $token]);

        // Load user's accessible cars for response - BOTH individual and company
        $cars = $user->accessibleCars()
                    ->with('latestLocation')
                    ->where('is_active', true)
                    ->get();

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => new UserResource($user),
            'company_cars' => CarResource::collection($cars) // Now includes individual user's car too
        ]);
     }
       public function logout(Request $request)
    {
        $user = $request->user();
        
        if ($user) {
            $user->update(['token' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    public function checkAuth(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => new UserResource($request->user())
        ]);
    }

    // ... rest of the methods remain the same
}