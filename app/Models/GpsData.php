<?php
// app/Models/GpsData.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GpsData extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'gps_data';

    protected $fillable = [
    'car_id',
    'latitude',
    'longitude',
    'speed',
    'heading',
    'altitude',
    'accuracy',
    'satellite_count',
    'ignition',      // CHANGED from device_battery
    'door_open',
    'fuel_cutoff',
    'voltage',       // ADD
    'snr',           // ADD
    'recorded_at'
    ];

protected $casts = [
    'door_open' => 'boolean',
    'fuel_cutoff' => 'boolean',
    'ignition' => 'boolean',    // CHANGED from device_battery
    'recorded_at' => 'datetime'
];

    // Relationships
    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}