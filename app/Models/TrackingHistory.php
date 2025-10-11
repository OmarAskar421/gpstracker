<?php
// app/Models/TrackingHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingHistory extends Model
{
    use HasFactory;

    protected $table = 'tracking_history';

    protected $fillable = [
        'car_id',
        'travel_date',
        'total_distance',
        'max_speed',
        'avg_speed',
        'travel_duration',
        'start_location',
        'end_location'
    ];

    protected $casts = [
        'travel_date' => 'date',
        'start_location' => 'array',
        'end_location' => 'array'
    ];

    // Relationships
    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}