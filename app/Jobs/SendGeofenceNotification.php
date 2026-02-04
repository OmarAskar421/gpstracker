<?php

namespace App\Jobs;

use App\Models\Car;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendGeofenceNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $carId,
        public float $latitude,
        public float $longitude,
        public $recordedAt
    ) {}

    public function handle(NotificationService $service)
    {
        $car = Car::find($this->carId);
        
        if ($car) {
            $service->sendGeofenceNotification(
                $car,
                $this->latitude,
                $this->longitude,
                $this->recordedAt
            );
        }
    }
}