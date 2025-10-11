<?php
// database/migrations/2024_01_15_000002_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('secret_code', 20)->unique();
            $table->string('phone_number', 15)->nullable();
            $table->string('full_name', 100);
            $table->string('email', 100)->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->string('token')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['secret_code']);
            $table->index(['company_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};