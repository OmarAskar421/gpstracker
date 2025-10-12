<?php
// database/migrations/2024_01_15_000006_create_geo_fence_events_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('geo_fence_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('geo_fence_id')->constrained()->onDelete('cascade');
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->enum('event_type', ['entry', 'exit']);
            $table->decimal('trigger_lat', 10, 8);
            $table->decimal('trigger_lng', 11, 8);
            $table->timestamp('recorded_at')->nullable();
            $table->timestamp('detected_at')->useCurrent();
            $table->boolean('is_processed')->default(false);

            $table->index(['car_id', 'is_processed']);
            $table->index(['detected_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('geo_fence_events');
    }
};