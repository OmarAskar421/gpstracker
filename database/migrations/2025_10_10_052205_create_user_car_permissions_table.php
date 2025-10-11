<?php
// database/migrations/2024_01_15_000009_create_user_car_permissions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_car_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->enum('permission_level', ['view', 'control', 'admin'])->default('view');
            $table->boolean('is_active')->default(true);
            $table->timestamp('granted_at')->useCurrent();

            $table->unique(['user_id', 'car_id']);
            $table->index(['user_id']);
            $table->index(['car_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_car_permissions');
    }
};