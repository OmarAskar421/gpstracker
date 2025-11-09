<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

   protected $fillable = [
        'company_id',
        'user_id',
        'car_name',
        'license_plate',
        'imei',
        'sim_number',
        'tracking_enabled',
        'alarm_enabled',
        'is_active'
    ];

   protected $casts = [
        'tracking_enabled' => 'boolean',
        'alarm_enabled' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gpsData()
    {
        return $this->hasMany(GpsData::class);
    }

    public function latestLocation()
    {
        return $this->hasOne(GpsData::class)->latest('recorded_at');
    }

    public function geoFence()
    {
        return $this->hasOne(GeoFence::class);    }

    public function alarms()
    {
        return $this->hasMany(Alarm::class);
    }

    public function trackingHistory()
    {
        return $this->hasMany(TrackingHistory::class);
    }

    public function userPermissions()
    {
        return $this->hasMany(UserCarPermission::class);
    }
}