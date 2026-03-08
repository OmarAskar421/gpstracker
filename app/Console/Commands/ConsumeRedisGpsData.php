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

                // Map incoming field names (both old and new) to database columns
                $fieldMap = [
                    'latitude'        => ['la', 'latitude'],
                    'longitude'       => ['lo', 'longitude'],
                    'speed'           => ['sp', 'speed'],
                    'heading'         => ['hd', 'heading'],
                    'altitude'        => ['al', 'altitude'],
                    'accuracy'        => ['ac', 'accuracy', 'hdop'],
                    'satellite_count' => ['sc', 'satellite_count'],
                    'ignition'        => ['ig', 'ignition'],
                    'voltage'         => ['vl', 'voltage'],
                    'snr'             => ['sn', 'snr', 'signal_quality'],
                    'door_open'       => ['do', 'door_open'],
                    'fuel_cutoff'     => ['fc', 'fuel_cutoff'],
                    'recorded_at'     => ['ts', 'recorded_at'],
                ];

                $validated = [];
                foreach ($fieldMap as $dest => $sources) {
                    $value = null;
                    foreach ($sources as $src) {
                        if (array_key_exists($src, $data)) {
                            $value = $data[$src];
                            break;
                        }
                    }
                    $validated[$dest] = $value;
                }

                // Set defaults for boolean fields if not present
                $validated['ignition']    = $validated['ignition'] ?? false;
                $validated['door_open']   = $validated['door_open'] ?? false;
                $validated['fuel_cutoff'] = $validated['fuel_cutoff'] ?? false;

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