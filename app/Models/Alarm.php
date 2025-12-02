<?php
// app/Models/Alarm.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alarm extends Model
{
    use HasFactory;
    public $timestamps = false;  // ← ADD THIS LINE

    protected $fillable = [
        'car_id',
        'alarm_type',
        'trigger_value',
        'latitude',
        'longitude',
        'severity',
        'is_acknowledged',
        'recorded_at'
    ];

    protected $casts = [
        'is_acknowledged' => 'boolean',
        'recorded_at' => 'datetime'
    ];

    // Relationships
    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}