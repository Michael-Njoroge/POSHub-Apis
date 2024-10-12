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
        Schema::create('pos_users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('last_ip_address')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('activation_code')->nullable();
            $table->string('forgotten_password_code')->nullable();
            $table->string('forgotten_password_time')->nullable();
            $table->string('remember_code')->nullable();
            $table->string('last_login')->nullable();
            $table->boolean('active')->default(1);
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('username');
            $table->string('gender')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
