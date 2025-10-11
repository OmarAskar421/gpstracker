<?php
// app/Http/Resources/UserResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\User;
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->full_name,
            'phone' => $this->phone_number,
            'email' => $this->email,
            'company_id' => $this->company_id,
            'is_active' => $this->is_active,
            // HIDDEN: secret_code, token (sensitive data)
        ];
    }
}