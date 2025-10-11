<?php
// database/migrations/2024_01_15_000003_create_cars_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('car_name', 100);
            $table->string('license_plate', 20)->nullable();
            $table->string('imei', 20)->unique();
            $table->string('sim_number', 20)->nullable();
            
            $table->boolean('tracking_enabled')->default(true);
            $table->boolean('alarm_enabled')->default(false);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['company_id']);
            $table->index(['user_id']);
            $table->index(['imei']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cars');
    }
};