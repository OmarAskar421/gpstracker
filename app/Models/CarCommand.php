<?php
// app/Models/CarCommand.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarCommand extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'command_type',
        'command_value', 
        'status',
        'response_message',
        'sent_at',
        'executed_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'executed_at' => 'datetime'
    ];

    // Relationships
    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}