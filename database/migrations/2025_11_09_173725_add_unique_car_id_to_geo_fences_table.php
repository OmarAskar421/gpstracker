<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // WARNING: This will fail if you already have
        // multiple geofences for a single car in your database.
        // You must clean your data first!
        Schema::table('geo_fences', function (Blueprint $table) {
            $table->unique('car_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('geo_fences', function (Blueprint $table) {
            $table->dropUnique(['car_id']);
        });
    }
};