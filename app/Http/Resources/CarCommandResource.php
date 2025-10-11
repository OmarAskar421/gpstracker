<?php
// app/Http/Resources/CarCommandResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CarCommandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'command_type' => $this->command_type,
            'command_value' => $this->command_value,
            'status' => $this->status,
            'response_message' => $this->response_message,
            'created_at' => $this->created_at->toISOString(),
            'sent_at' => $this->sent_at?->toISOString(),
            'executed_at' => $this->executed_at?->toISOString(),
        ];
    }
}