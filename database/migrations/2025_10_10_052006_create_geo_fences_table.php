<?php
// database/migrations/2024_01_15_000005_create_geo_fences_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('geo_fences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->string('fence_name', 100);
            $table->enum('fence_type', ['circle', 'polygon'])->default('circle');
            
            $table->decimal('center_lat', 10, 8)->nullable();
            $table->decimal('center_lng', 11, 8)->nullable();
            $table->decimal('radius', 8, 2)->nullable();
            
            $table->json('polygon_coordinates')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->integer('alert_delay')->default(300);
            $table->timestamps();

            $table->index(['car_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('geo_fences');
    }
};