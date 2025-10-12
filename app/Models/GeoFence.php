<?php
// app/Models/GeoFence.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoFence extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'fence_name',
        'fence_type',
        'center_lat',
        'center_lng',
        'radius',
        'polygon_coordinates',
        'is_active',
        'alert_delay'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'polygon_coordinates' => 'array'
    ];

    // Relationships
    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function events()
    {
        return $this->hasMany(GeoFenceEvent::class);
    }
}