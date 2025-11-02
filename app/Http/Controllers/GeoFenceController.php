<?php

namespace App\Http\Controllers;

use App\Models\GeoFence; // We already have this model
use App\Http\Resources\GeoFenceResource; // We already have this resource
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GeoFenceController extends Controller
{
    /**
     * List all geofences for a specific car.
     * (Called by Flutter app)
     *
     * GET /api/cars/{carId}/geofences
     */
    public function index(Request $request)
    {
        // The 'car' object is injected by our 'can.access.car' middleware
        $car = $request->car;
        
        $fences = $car->geoFences()->get();
        
        return GeoFenceResource::collection($fences);
    }

    /**
     * Create a new geofence for a car.
     * (Called by Flutter app)
     *
     * POST /api/cars/{carId}/geofences
     */
    public function store(Request $request)
    {
        // The 'car' object is injected by 'can.access.car' middleware
        $car = $request->car;

        $validated = $request->validate([
            'fence_name' => 'required|string|max:100',
            'fence_type' => ['required', Rule::in(['circle', 'polygon'])],
            'is_active' => 'sometimes|boolean',
            
            // Circle validation
            'center_lat' => 'required_if:fence_type,circle|numeric|min:-90|max:90',
            'center_lng' => 'required_if:fence_type,circle|numeric|min:-180|max:180',
            'radius' => 'required_if:fence_type,circle|numeric|min:1', // Min 1 meter radius

            // Polygon validation
            'polygon_coordinates' => 'required_if:fence_type,polygon|array|min:3', // Min 3 points for a polygon
            'polygon_coordinates.*' => 'array|size:2', // Each point must be [lat, lng]
            'polygon_coordinates.*.0' => 'numeric|min:-90|max:90', // Latitude
            'polygon_coordinates.*.1' => 'numeric|min:-180|max:180', // Longitude
        ]);

        $fence = $car->geoFences()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Geofence created successfully',
            'geofence' => new GeoFenceResource($fence)
        ], 201);
    }

    /**
     * Update a geofence (e.g., toggle active status).
     * (Called by Flutter app)
     *
     * PUT /api/cars/{carId}/geofences/{fenceId}
     */
    public function update(Request $request, $carId, $fenceId)
    {
        // Middleware confirms we can access the car.
        // We just need to find the specific fence for that car.
        $fence = GeoFence::where('id', $fenceId)
                         ->where('car_id', $request->car->id)
                         ->firstOrFail();
        
        $validated = $request->validate([
            'fence_name' => 'sometimes|string|max:100',
            'is_active' => 'sometimes|boolean',
            // Add other fields here if you want to allow editing them
        ]);

        $fence->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Geofence updated',
            'geofence' => new GeoFenceResource($fence)
        ]);
    }

    /**
     * Delete a geofence.
     * (Called by Flutter app)
     *
     * DELETE /api/cars/{carId}/geofences/{fenceId}
     */
    public function destroy(Request $request, $carId, $fenceId)
    {
        // Middleware confirms we can access the car.
        $fence = GeoFence::where('id', $fenceId)
                         ->where('car_id', $request->car->id)
                         ->firstOrFail();
        
        $fence->delete();

        return response()->json(['success' => true, 'message' => 'Geofence deleted']);
    }
}