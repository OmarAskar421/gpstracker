<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'car_id' => $this->car_id,
            'car_name' => $this->car->car_name ?? null,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
            'date' => $this->created_at->format('Y-m-d'), // Separate date field
            'time' => $this->created_at->format('H:i:s'), // Separate time field
            'timestamp' => $this->created_at->toISOString(), // Full ISO timestamp
        ];
    }
}