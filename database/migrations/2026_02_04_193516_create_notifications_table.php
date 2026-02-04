<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->string('type'); // 'geofence_exit', 'geofence_entry', 'speed_alert', etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data (lat, lng, speed, etc.)
            $table->timestamp('created_at');
            
            $table->index(['user_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};