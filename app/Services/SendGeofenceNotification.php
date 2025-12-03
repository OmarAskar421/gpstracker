<?php

namespace App\Jobs;

use App\Models\Car;
use App\Models\Alarm;
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
        public Car $car,
        public Alarm $alarm
    ) {}

    public function handle(NotificationService $service)
    {
        $service->sendGeofenceNotification($this->car, $this->alarm);
    }
}