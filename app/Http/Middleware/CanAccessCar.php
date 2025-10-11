<?php
// app/Http/Middleware/CanAccessCar.php

namespace App\Http\Middleware;

use App\Models\Car;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanAccessCar
{
    public function handle(Request $request, Closure $next, string $permission = 'view'): Response
    {
        $carId = $request->route('carId') ?? $request->route('id');
        
        if (!$carId) {
            return response()->json([
                'success' => false,
                'message' => 'Car ID is required'
            ], 400);
        }

        $car = Car::where('id', $carId)->where('is_active', true)->first();

        if (!$car) {
            return response()->json([
                'success' => false,
                'message' => 'Car not found'
            ], 404);
        }

        $user = $request->user();

        if (!$this->userHasPermission($user, $car, $permission)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to access this car'
            ], 403);
        }

        // Add car to request for controller use
        $request->merge(['car' => $car]);

        return $next($request);
    }

    private function userHasPermission($user, $car, $permissionLevel): bool
    {
        // Company user can access all company cars
        if ($user->company_id && $car->company_id == $user->company_id) {
            return true;
        }

        // Individual user can access their own cars
        if (!$user->company_id && $car->user_id == $user->id) {
            return true;
        }

        // Check user_car_permissions table for explicit permissions
        $permission = $user->carPermissions()
                          ->where('car_id', $car->id)
                          ->where('is_active', true)
                          ->first();

        if ($permission) {
            switch ($permissionLevel) {
                case 'view':
                    return in_array($permission->permission_level, ['view', 'control', 'admin']);
                case 'control':
                    return in_array($permission->permission_level, ['control', 'admin']);
                case 'admin':
                    return $permission->permission_level === 'admin';
            }
        }

        return false;
    }
}