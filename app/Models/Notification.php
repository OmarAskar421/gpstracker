<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    public $timestamps = false; // We only use created_at

    protected $fillable = [
        'user_id',
        'car_id',
        'type',
        'title',
        'message',
        'data',
        'created_at'
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}