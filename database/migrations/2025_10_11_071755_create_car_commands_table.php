<?php
// database/migrations/2024_01_15_000010_create_car_commands_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('car_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained()->onDelete('cascade');
            $table->enum('command_type', ['fuel_cutoff', 'microphone_control', 'alarm_control']);
            $table->string('command_value'); // 'on', 'off', 'arm', 'disarm'
            $table->enum('status', ['pending', 'sent', 'executed', 'failed'])->default('pending');
            $table->text('response_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('executed_at')->nullable();
            $table->timestamps();

            $table->index(['car_id', 'status']);
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('car_commands');
    }
};