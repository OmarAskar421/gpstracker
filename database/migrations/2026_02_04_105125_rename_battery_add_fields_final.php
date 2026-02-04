<?php
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        // Rename device_battery to ignition and change to boolean
        DB::statement('ALTER TABLE gps_data CHANGE device_battery ignition BOOLEAN DEFAULT FALSE');
        
        // Add voltage if not exists
        if (!\Schema::hasColumn('gps_data', 'voltage')) {
            DB::statement('ALTER TABLE gps_data ADD voltage DECIMAL(5,2) NULL AFTER ignition');
        }
        
        // Add snr if not exists
        if (!\Schema::hasColumn('gps_data', 'snr')) {
            DB::statement('ALTER TABLE gps_data ADD snr DECIMAL(5,2) NULL AFTER voltage');
        }
    }
    
    public function down(): void {
        DB::statement('ALTER TABLE gps_data DROP COLUMN IF EXISTS snr');
        DB::statement('ALTER TABLE gps_data DROP COLUMN IF EXISTS voltage');
        DB::statement('ALTER TABLE gps_data CHANGE ignition device_battery DECIMAL(5,2) NULL');
    }
};