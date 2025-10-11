<?php
// database/seeders/GpsTrackerSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GpsTrackerSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks temporarily
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        // Clear existing data
        DB::table('user_car_permissions')->truncate();
        DB::table('tracking_history')->truncate();
        DB::table('alarms')->truncate();
        DB::table('geo_fence_events')->truncate();
        DB::table('geo_fences')->truncate();
        DB::table('gps_data')->truncate();
        DB::table('cars')->truncate();
        DB::table('users')->truncate();
        DB::table('companies')->truncate();

        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Create Company
        $companyId = DB::table('companies')->insertGetId([
            'company_name' => 'Demo Transport Company',
            'contact_person' => 'John Manager',
            'phone_number' => '+1234567890',
            'email' => 'demo@company.com',
            'address' => '123 Business St, City, Country',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create Users
        $companyUserId = DB::table('users')->insertGetId([
            'secret_code' => 'COMPANY123',
            'phone_number' => '+1234567891',
            'full_name' => 'Company User',
            'email' => 'user@company.com',
            'company_id' => $companyId,
            'token' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $individualUserId = DB::table('users')->insertGetId([
            'secret_code' => 'INDIVIDUAL456',
            'phone_number' => '+1234567892',
            'full_name' => 'Individual User',
            'email' => 'individual@email.com',
            'company_id' => null,
            'token' => null,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Create Cars
        $companyCar1Id = DB::table('cars')->insertGetId([
            'company_id' => $companyId,
            'user_id' => null,
            'car_name' => 'Delivery Van 1',
            'license_plate' => 'VAN001',
            'imei' => '123456789012345',
            'sim_number' => '+1111111111',
            'tracking_enabled' => true,
            'alarm_enabled' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $companyCar2Id = DB::table('cars')->insertGetId([
            'company_id' => $companyId,
            'user_id' => null,
            'car_name' => 'Delivery Van 2',
            'license_plate' => 'VAN002',
            'imei' => '123456789012346',
            'sim_number' => '+1111111112',
            'tracking_enabled' => true,
            'alarm_enabled' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $personalCarId = DB::table('cars')->insertGetId([
            'company_id' => null,
            'user_id' => $individualUserId,
            'car_name' => 'My Personal Car',
            'license_plate' => 'PERS123',
            'imei' => '123456789012347',
            'sim_number' => '+1111111113',
            'tracking_enabled' => true,
            'alarm_enabled' => false,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Create GPS Data - FIXED: Remove created_at
        DB::table('gps_data')->insert([
            [
                'car_id' => $companyCar1Id,
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'speed' => 45.5,
                'heading' => 90.0,
                'altitude' => 50.0,
                'accuracy' => 5.0,
                'satellite_count' => 8,
                'device_battery' => 85.5,
                'door_open' => false,
                'fuel_cutoff' => false,
                'recorded_at' => now()->subMinutes(5),
                'received_at' => now(),
                // REMOVED: 'created_at' => now(),
            ],
            [
                'car_id' => $personalCarId,
                'latitude' => 40.7130,
                'longitude' => -74.0062,
                'speed' => 0.0,
                'heading' => 0.0,
                'altitude' => 25.0,
                'accuracy' => 3.5,
                'satellite_count' => 10,
                'device_battery' => 92.0,
                'door_open' => true,
                'fuel_cutoff' => false,
                'recorded_at' => now()->subMinutes(2),
                'received_at' => now(),
                // REMOVED: 'created_at' => now(),
            ]
        ]);

        $this->command->info('✅ Demo data created successfully!');
        $this->command->info('🔑 Company User Secret Code: COMPANY123');
        $this->command->info('🔑 Individual User Secret Code: INDIVIDUAL456');
    }
}