<?php
// app/Http/Resources/CarResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->car_name,
            'license_plate' => $this->license_plate,
            'imei' => $this->imei,
            'tracking_enabled' => $this->tracking_enabled,
            'alarm_enabled' => $this->alarm_enabled,
            'is_active' => $this->is_active,
            'last_location' => new GpsDataResource($this->whenLoaded('latestLocation')),
            'created_at' => $this->created_at,
            // HIDDEN: sim_number, company_id, user_id (sensitive/internal)
        ];
    }
}