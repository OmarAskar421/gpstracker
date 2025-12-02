<?php
// app/Models/GeoFenceEvent.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeoFenceEvent extends Model
{
    use HasFactory;

    protected $table = 'geo_fence_events';
    public $timestamps = false;  // ← ADD THIS LINE

    protected $fillable = [
        'geo_fence_id',
        'car_id',
        'event_type',
        'trigger_lat',
        'trigger_lng',
        'recorded_at',
        'is_processed'
    ];

    protected $casts = [
        'is_processed' => 'boolean',
        'recorded_at' => 'datetime'
    ];

    // Relationships
    public function geoFence()
    {
        return $this->belongsTo(GeoFence::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}