<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Services\GpsDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TrackerController extends Controller
{
    protected GpsDataService $gpsDataService;

    public function __construct(GpsDataService $gpsDataService)
    {
        $this->gpsDataService = $gpsDataService;
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
            'ignition' => 'sometimes|boolean',
            'door_open' => 'sometimes|boolean',
            'fuel_cutoff' => 'sometimes|boolean',
            'voltage' => 'sometimes|numeric',
            'snr' => 'sometimes|numeric',
            'recorded_at' => 'sometimes|date',
        ]);

        try {
            $car = Car::where('imei', $validated['imei'])->firstOrFail();
            
            // If heading is outside valid range (0-360), set it to 360
            if (isset($validated['heading']) && ($validated['heading'] < 0 || $validated['heading'] > 360)) {
                Log::warning('Invalid heading from HTTP tracker, setting to 360', [
                    'imei' => $validated['imei'], 
                    'original_heading' => $validated['heading']
                ]);
                $validated['heading'] = 360;
            }
            
            // Use the service to process the data
            $this->gpsDataService->processGpsData($car, $validated);

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Tracker error: ' . $e->getMessage());
            return response()->json(['status' => 'failed'], 500);
        }
    }
}