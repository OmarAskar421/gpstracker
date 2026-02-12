<?php

namespace App\Services;

use App\Models\Car;
use App\Models\GpsData;
use App\Jobs\SendGeofenceNotification;
use App\Services\GeoFenceService;
use Illuminate\Support\Facades\Log;

class GpsDataService
{
    protected GeoFenceService $geoFenceService;

    public function __construct(GeoFenceService $geoFenceService)
    {
        $this->geoFenceService = $geoFenceService;
    }

    /**
     * Process incoming GPS data (common logic for HTTP and MQTT)
     */
    public function processGpsData(Car $car, array $validatedData): GpsData
    {
        // Get previous point BEFORE inserting new one
        $lastGpsData = $car->gpsData()->latest('recorded_at')->first();

        // Create new GPS record
        $gpsData = $car->gpsData()->create($validatedData);

        // Geofence check (if car has an active geofence)
        if ($car->geoFence && $car->geoFence->is_active) {
            $this->checkGeoFence($car, $gpsData, $lastGpsData);
        }

        // You can add more logic here (alarms, tracking history, etc.)

        return $gpsData;
    }

    /**
     * Check if car exited geofence and trigger notifications
     */
    protected function checkGeoFence(Car $car, GpsData $newData, ?GpsData $lastData): void
    {
        if (!$lastData) {
            return;
        }

        $wasInside = $this->geoFenceService->isPointInCircle($lastData, $car->geoFence);
        $isInside = $this->geoFenceService->isPointInCircle($newData, $car->geoFence);

        if ($wasInside && !$isInside) {
            $this->triggerGeofenceExit($car, $newData);
        }
    }

    /**
     * Create geofence event and dispatch notification job
     */
    protected function triggerGeofenceExit(Car $car, GpsData $location): void
    {
        // Store event
        \App\Models\GeoFenceEvent::create([
            'geo_fence_id' => $car->geoFence->id,
            'car_id' => $car->id,
            'event_type' => 'exit',
            'trigger_lat' => $location->latitude,
            'trigger_lng' => $location->longitude,
            'recorded_at' => $location->recorded_at,
            'is_processed' => false,
        ]);

        // Dispatch notification job (non-blocking)
        SendGeofenceNotification::dispatch(
            $car->id,
            (float) $location->latitude,
            (float) $location->longitude,
            $location->recorded_at
        );
    }
}