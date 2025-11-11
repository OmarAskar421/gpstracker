<?php
// app/Http/Controllers/TrackerController.php
namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\GpsData;
use App\Models\GeoFenceEvent;
use App\Models\Alarm;
// use App\Services\GeoFenceService; // We'll use this later
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // For logging

class TrackerController extends Controller
{
    /**
     * Store data from ESP32 tracker
     * This is the main endpoint for your devices
     *
     * POST /api/tracker/data
     */
    public function storeData(Request $request)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'imei' => 'required|string|exists:cars,imei',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'speed' => 'sometimes|numeric',
            'heading' => 'sometimes|numeric',
            'altitude' => 'sometimes|numeric',
            'accuracy' => 'sometimes|numeric',
            'satellite_count' => 'sometimes|integer',
            'device_battery' => 'sometimes|numeric',
            'door_open' => 'sometimes|boolean',
            'fuel_cutoff' => 'sometimes|boolean',
            'recorded_at' => 'sometimes|date',
        ]);

        try {
            // 2. Find the car using the validated IMEI
            $car = Car::where('imei', $validated['imei'])->first();

            // 3. Save the new GPS data point
            $gpsData = $car->gpsData()->create($validated);

            // 4. --- GEOFENCE CHECK ---
            // (Geofence logic will go here later)
            // $lastGpsData = $car->gpsData()->...
            // $this->checkGeoFenceForAlarm($car, $gpsData, $lastGpsData);

            // 5. Respond to the ESP32
            // We just return success. All command logic is removed.
            return response()->json([
                'status' => 'success'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to store tracker data: ' . $e->getMessage(), $request->all());
            return response()->json(['status' => 'failed', 'message' => 'Server error'], 500);
        }
    }

    /**
     * Helper to check geofence (currently empty)
     */
    private function checkGeoFenceForAlarm(Car $car, GpsData $newData, ?GpsData $lastData)
    {
        // We will add logic here later when you want geofence checking.
        // For now, it does nothing.
    }
}