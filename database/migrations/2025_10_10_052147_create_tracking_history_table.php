<?php
// database/migrations/2024_01_15_000008_create_tracking_history_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tracking_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->date('travel_date');
            $table->decimal('total_distance', 8, 2)->default(0);
            $table->decimal('max_speed', 5, 2)->default(0);
            $table->decimal('avg_speed', 5, 2)->default(0);
            $table->integer('travel_duration')->default(0);
            $table->json('start_location')->nullable();
            $table->json('end_location')->nullable();
            $table->timestamps();

            $table->unique(['car_id', 'travel_date']);
            $table->index(['travel_date']);
            $table->index(['car_id', 'travel_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('tracking_history');
    }
};