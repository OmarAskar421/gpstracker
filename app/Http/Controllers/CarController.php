<?php
// app/Http/Controllers/CarController.php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Http\Resources\CarResource;
use App\Http\Resources\GpsDataResource;
use Illuminate\Http\Request;

class CarController extends Controller
{
    /**
     * Get user's accessible cars
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Safety check
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }

        $cars = $user->accessibleCars()
                    ->with('latestLocation')
                    ->where('is_active', true)
                    ->get();

           return response()->json([
            'success' => true, // <-- ADDED 'success' key
            'cars' => CarResource::collection($cars)
        ]);
    }

    /**
     * Enable/disable tracking for a car
     */
    public function updateTracking(Request $request)
    {
        $car = $request->car;

        $request->validate([
            'enabled' => 'required|boolean'
        ]);

        $car->update(['tracking_enabled' => $request->enabled]);

        return response()->json([
            'success' => true,
            'message' => 'Tracking ' . ($request->enabled ? 'enabled' : 'disabled') . ' successfully'
        ]);
    }

    /**
     * Enable/disable alarm for a car
     */
    public function updateAlarm(Request $request)
    {
        $car = $request->car;

        $request->validate([
            'enabled' => 'required|boolean'
        ]);

        $car->update(['alarm_enabled' => $request->enabled]);

        return response()->json([
            'success' => true,
            'message' => 'Alarm ' . ($request->enabled ? 'enabled' : 'disabled') . ' successfully'
        ]);
    }

    /**
     * Get live location of a car
     */
    public function liveLocation(Request $request)
    {
        $car = $request->car;
        $latestLocation = $car->latestLocation;

        if (!$latestLocation) {
            return response()->json([
                'success' => false,
                'message' => 'No location data available'
            ], 404);
        }

        return response()->json([ 
          'success' => true,
          'location' => new GpsDataResource($latestLocation)
        ]);
    }

    /**
     * Get location history for a car
     */
    public function locationHistory(Request $request)
    {
        $car = $request->car;

        $request->validate([
            'date' => 'sometimes|date'
        ]);

        $date = $request->date ?? now()->format('Y-m-d');

        $locations = $car->gpsData()
                        ->whereDate('recorded_at', $date)
                        ->where('speed', '>', 1.5)   // FILTER STOPPED POINTS
                        ->orderBy('recorded_at', 'asc')
                        ->get();

        // Calculate total distance (simplified)
        $totalDistance = $this->calculateTotalDistance($locations);

        return response()->json([
            'car_id' => $car->id,
            'car_name' => $car->car_name,
            'date' => $date,
            'total_distance' => round($totalDistance, 2),
            'max_speed' => (double)($locations->max('speed') ?? 0),
            'locations_count' => $locations->count(),
            'locations' => GpsDataResource::collection($locations)
        ]);
    }

    /**
     * Check door status
     */
    public function doorStatus(Request $request)
    {
        $car = $request->car;
        $latestLocation = $car->latestLocation;

        return response()->json([
            'success' => true,
            'door_open' => $latestLocation ? $latestLocation->door_open : false,
            'timestamp' => $latestLocation ? $latestLocation->recorded_at->toISOString() : null
        ]);
    }

    /**
     * Check fuel cutoff status
     */
    public function fuelCutoff(Request $request)
    {
        $car = $request->car;
        $latestLocation = $car->latestLocation;

        return response()->json([
            'success' => true,
            'fuel_cutoff' => $latestLocation ? $latestLocation->fuel_cutoff : false,
            'timestamp' => $latestLocation ? $latestLocation->recorded_at->toISOString() : null
        ]);
    }

    /**
     * Calculate total distance from GPS points (simplified)
     */
    private function calculateTotalDistance($locations)
    {
        $totalDistance = 0;
        $previousLocation = null;
        
        foreach ($locations as $location) {
            if ($previousLocation) {
                // Simple distance calculation - improve with Haversine formula later
                $latDiff = abs($location->latitude - $previousLocation->latitude);
                $lngDiff = abs($location->longitude - $previousLocation->longitude);
                $distance = sqrt($latDiff * $latDiff + $lngDiff * $lngDiff) * 111; // Rough km conversion
                $totalDistance += $distance;
            }
            $previousLocation = $location;
        }

        return $totalDistance;
    }
}