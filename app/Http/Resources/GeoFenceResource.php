<?php
// app/Http/Resources/GeoFenceResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GeoFenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->fence_name,
            'type' => $this->fence_type,
            'center_lat' => $this->center_lat ? (float) $this->center_lat : null,
            'center_lng' => $this->center_lng ? (float) $this->center_lng : null,
            'radius' => $this->radius ? (float) $this->radius : null,
            'polygon_coordinates' => $this->polygon_coordinates,
            'is_active' => $this->is_active,
            'alert_delay' => $this->alert_delay,
            'created_at' => $this->created_at,
        ];
    }
}