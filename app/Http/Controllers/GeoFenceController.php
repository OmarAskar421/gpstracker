<?php
// app/Http/Controllers/GeoFenceController.php

namespace App\Http\Controllers;

use App\Models\GeoFence;
use App\Http\Resources\GeoFenceResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Car; // Make sure Car model is imported

class GeoFenceController extends Controller
{
    /**
     * Get the single geofence for a car.
     *
     * GET /api/cars/{carId}/geofence
     */
    public function show(Request $request)
    {
        $car = $request->car; // Injected by CanAccessCar middleware

        // Use the new 'geoFence' singular relationship
        $fence = $car->geoFence;

        if (!$fence) {
            return response()->json(['success' => false, 'message' => 'No geofence set for this car.'], 404);
        }
        
        return new GeoFenceResource($fence);
    }

    /**
     * Create or update the single geofence for a car.
     *
     * POST /api/cars/{carId}/geofence
     */
    public function storeOrUpdate(Request $request)
    {
        $car = $request->car; // Injected by CanAccessCar middleware

        $validated = $request->validate([
            'fence_name' => 'required|string|max:100',
            'fence_type' => ['required', Rule::in(['circle', 'polygon'])],
            'is_active' => 'sometimes|boolean',
            
            // Circle validation
            'center_lat' => 'required_if:fence_type,circle|numeric|min:-90|max:90',
            'center_lng' => 'required_if:fence_type,circle|numeric|min:-180|max:180',
            'radius' => 'required_if:fence_type,circle|numeric|min:1',

            // Polygon validation
            'polygon_coordinates' => 'required_if:fence_type,polygon|array|min:3',
            'polygon_coordinates.*' => 'array|size:2',
            'polygon_coordinates.*.0' => 'numeric|min:-90|max:90',
            'polygon_coordinates.*.1' => 'numeric|min:-180|max:180',
        ]);

        // This is the key logic:
        // 1. Look for a geofence where 'car_id' is the car's ID.
        // 2. If it exists, update it with the new $validated data.
        // 3. If it does not exist, create it with the $validated data.
        $fence = GeoFence::updateOrCreate(
            ['car_id' => $car->id],
            $validated
        );

        return response()->json([
            'success' => true,
            'message' => 'Geofence saved successfully',
            'geofence' => new GeoFenceResource($fence)
        ], 200); // 200 OK because it could be an update
    }

    /**
     * Delete the single geofence for a car.
     *
     * DELETE /api/cars/{carId}/geofence
     */
    public function destroy(Request $request)
    {
        $car = $request->car; // Injected by CanAccessCar middleware

        // Use the relationship to find and delete the geofence
        $fence = $car->geoFence;

        if ($fence) {
            $fence->delete();
            return response()->json(['success' => true, 'message' => 'Geofence deleted']);
        }

        return response()->json(['success' => false, 'message' => 'No geofence to delete'], 404);
    }
}