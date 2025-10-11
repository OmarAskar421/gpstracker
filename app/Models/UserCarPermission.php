<?php
// app/Models/UserCarPermission.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCarPermission extends Model
{
    use HasFactory;

    protected $table = 'user_car_permissions';

    protected $fillable = [
        'user_id',
        'car_id',
        'permission_level',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
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