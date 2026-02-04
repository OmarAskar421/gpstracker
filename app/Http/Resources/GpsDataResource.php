<?php
// app/Http/Resources/GpsDataResource.php

namespace App\Http\Resources;

// Make sure Carbon is imported
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GpsDataResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
    'latitude' => (float) $this->latitude,
    'longitude' => (float) $this->longitude,
    'speed' => $this->speed ? (float) $this->speed : null,
    'heading' => $this->heading ? (float) $this->heading : null,
    'altitude' => $this->altitude ? (float) $this->altitude : null,
    'accuracy' => $this->accuracy ? (float) $this->accuracy : null,
    'ignition' => $this->ignition,              // CHANGED from battery
    'door_open' => $this->door_open,
    'fuel_cutoff' => $this->fuel_cutoff,
    'voltage' => $this->voltage ? (float) $this->voltage : null,    // ADD
    'snr' => $this->snr ? (float) $this->snr : null,                // ADD
    'timestamp' => $this->recorded_at ? Carbon::parse($this->recorded_at)->toISOString() : null,
];
    }
}