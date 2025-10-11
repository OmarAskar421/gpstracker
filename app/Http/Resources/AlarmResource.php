<?php
// app/Http/Resources/AlarmResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AlarmResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'car_id' => $this->car_id,
            'car_name' => $this->car->car_name ?? null,
            'type' => $this->alarm_type,
            'trigger_value' => $this->trigger_value ? (float) $this->trigger_value : null,
            'location' => [
                'latitude' => $this->latitude ? (float) $this->latitude : null,
                'longitude' => $this->longitude ? (float) $this->longitude : null,
            ],
            'severity' => $this->severity,
            'acknowledged' => $this->is_acknowledged,
            'timestamp' => $this->recorded_at?->toISOString(),
            'received_at' => $this->received_at?->toISOString(),
        ];
    }
}