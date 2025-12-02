<?php
namespace App\Http\Controllers;
use App\Models\Car;
use App\Models\GpsData;
use App\Models\GeoFenceEvent;
use App\Models\Alarm;
use App\Services\GeoFenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackerController extends Controller
{
    protected GeoFenceService $geoFenceService;

    public function __construct(GeoFenceService $geoFenceService)
    {
        $this->geoFenceService = $geoFenceService;
    }

    public function storeData(Request $request)
    {
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
        $car = Car::where('imei', $validated['imei'])->first();
        
        // FIX: Get PREVIOUS location BEFORE saving new point
        $lastGpsData = $car->gpsData()->latest('recorded_at')->first();
        
        $gpsData = $car->gpsData()->create($validated);

        if ($car->geoFence && $car->geoFence->is_active) {
            $this->checkGeoFenceForAlarm($car, $gpsData, $lastGpsData);
        }

        return response()->json(['status' => 'success']);

    } catch (\Exception $e) {
        Log::error('Geofence error: ' . $e->getMessage());
        return response()->json(['status' => 'failed'], 500);
    }
    }

    private function checkGeoFenceForAlarm(Car $car, GpsData $newData, ?GpsData $lastData)
    {
        if (!$lastData) return;

        $wasInside = $this->geoFenceService->isPointInCircle($lastData, $car->geoFence);
        $isInside = $this->geoFenceService->isPointInCircle($newData, $car->geoFence);

        if ($wasInside && !$isInside) {
            $this->triggerGeofenceExit($car, $newData);
        }
    }

    private function triggerGeofenceExit(Car $car, GpsData $location)
    {
        // Create event & alarm records
        GeoFenceEvent::create([
            'geo_fence_id' => $car->geoFence->id,
            'car_id' => $car->id,
            'event_type' => 'exit',
            'trigger_lat' => $location->latitude,
            'trigger_lng' => $location->longitude,
            'recorded_at' => $location->recorded_at,
            'is_processed' => false,
        ]);

        Alarm::create([
            'car_id' => $car->id,
            'alarm_type' => 'geofence',
            'latitude' => $location->latitude,
            'longitude' => $location->longitude,
            'severity' => 'high',
            'is_acknowledged' => false,
            'recorded_at' => $location->recorded_at,
        ]);

        // Step 5 will add notification dispatch here
    }
}