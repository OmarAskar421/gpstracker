<?php
// database/migrations/2024_01_15_000007_create_alarms_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alarms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->enum('alarm_type', ['speed', 'vibration', 'towing', 'geofence', 'panic']);
            $table->decimal('trigger_value', 8, 2)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->boolean('is_acknowledged')->default(false);
            $table->timestamp('recorded_at')->nullable();
            $table->timestamp('received_at')->useCurrent();

            $table->index(['car_id', 'is_acknowledged']);
            $table->index(['recorded_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('alarms');
    }
};