<?php

namespace App\Console\Commands;

use App\Models\Car;
use App\Services\GpsDataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ConsumeRedisGpsData extends Command
{
    protected $signature = 'gps:consume-redis 
                            {--timeout=0 : Seconds to run before exiting (0 = infinite)} 
                            {--limit=0 : Max messages to process before exiting (0 = infinite)}';
    
    protected $description = 'Consume GPS data from Redis list and store in database';

    protected GpsDataService $gpsDataService;

    public function __construct(GpsDataService $gpsDataService)
    {
        parent::__construct();
        $this->gpsDataService = $gpsDataService;
    }

    public function handle()
    {
        $redisKey = 'raw_gps';
        $timeout = (int) $this->option('timeout');
        $limit = (int) $this->option('limit');
        
        $startTime = time();
        $processed = 0;

        $this->info("Starting Redis consumer. Listening to list: {$redisKey}");

        while (true) {
            // Check exit conditions
            if ($timeout > 0 && (time() - $startTime) >= $timeout) {
                $this->info("Timeout reached, exiting.");
                break;
            }
            if ($limit > 0 && $processed >= $limit) {
                $this->info("Processed {$limit} messages, exiting.");
                break;
            }

            // Block until a message arrives (0 = forever)
            $result = Redis::blpop($redisKey, 0);
            
            if (!$result) {
                continue; // should not happen, but safety
            }

            // $result[0] = key name, $result[1] = value
            $payload = json_decode($result[1], true);
            
            if (!$payload || !isset($payload['imei'], $payload['data'])) {
                Log::warning('Invalid Redis payload', ['payload' => $result[1]]);
                continue;
            }

            $imei = $payload['imei'];
            $data = $payload['data'];

            try {
                $car = Car::where('imei', $imei)->where('is_active', true)->first();

                if (!$car) {
                    Log::warning('Car not found for IMEI', ['imei' => $imei]);
                    continue;
                }

                // Prepare data for the service (same fields as HTTP endpoint)
                $validated = [
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'speed' => $data['speed'] ?? null,
                    'heading' => $data['heading'] ?? null,
                    'altitude' => $data['altitude'] ?? null,
                    'accuracy' => $data['accuracy'] ?? null,
                    'satellite_count' => $data['satellite_count'] ?? null,
                    'ignition' => $data['ignition'] ?? false,
                    'door_open' => $data['door_open'] ?? false,
                    'fuel_cutoff' => $data['fuel_cutoff'] ?? false,
                    'voltage' => $data['voltage'] ?? null,
                    'snr' => $data['snr'] ?? null,
                    'recorded_at' => $data['recorded_at'] ?? null,
                ];

                // Remove null values (optional, depends on your model)
                $validated = array_filter($validated, function ($value) {
                    return $value !== null;
                });

                $this->gpsDataService->processGpsData($car, $validated);
                
                $processed++;
                
                if ($processed % 100 == 0) {
                    $this->info("Processed {$processed} messages");
                }

            } catch (\Exception $e) {
                Log::error('Redis consumer error', [
                    'imei' => $imei,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        $this->info("Consumer stopped. Total processed: {$processed}");
        return 0;
    }
}