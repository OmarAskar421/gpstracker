<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'secret_code',
        'phone_number',
        'full_name',
        'email',
        'company_id',
        'token',
        'fcm_token',              // ADD THIS
        'push_notifications_enabled', // ADD THIS
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function personalCars()
    {
        return $this->hasMany(Car::class, 'user_id');
    }

    public function carPermissions()
    {
        return $this->hasMany(UserCarPermission::class);
    }

    public function accessibleCars()
    {
        // Cars the user can access (personal + company + permissions)
        if ($this->company_id) {
            // Company user - can access all company cars + permitted cars
            return Car::where(function($query) {
                $query->where('company_id', $this->company_id)
                      ->orWhereIn('id', $this->carPermissions()->pluck('car_id'));
            });
        } else {
            // Individual user - personal cars + permitted cars
            return Car::where(function($query) {
                $query->where('user_id', $this->id)
                      ->orWhereIn('id', $this->carPermissions()->pluck('car_id'));
            });
        }
    }
}