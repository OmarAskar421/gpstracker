<?php
// database/migrations/2024_01_15_000004_create_gps_data_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gps_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('speed', 5, 2)->nullable();
            $table->decimal('heading', 5, 2)->nullable();
            $table->decimal('altitude', 6, 2)->nullable();
            
            $table->decimal('accuracy', 4, 2)->nullable();
            $table->integer('satellite_count')->nullable();
            $table->decimal('device_battery', 5, 2)->nullable();
            
            $table->boolean('door_open')->default(false);
            $table->boolean('fuel_cutoff')->default(false);
            
            $table->timestamp('recorded_at')->nullable();
            $table->timestamp('received_at')->useCurrent();

            $table->index(['car_id', 'recorded_at']);
            $table->index(['recorded_at']);
            $table->index(['car_id', 'received_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('gps_data');
    }
};