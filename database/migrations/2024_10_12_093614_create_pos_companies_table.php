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
        Schema::create('pos_companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('group_name');
            $table->foreignUuid('group_id')->nullable()->references('id')->on('pos_groups')->onDelete('cascade');
            $table->string('name');
            $table->string('address');
            $table->string('phone');
            $table->string('email');
            $table->string('logo')->nullable();
            $table->string('company')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            $table->string('password')->nullable();
            $table ->dateTime('last_login')->nullable();   
            $table->string('vat_no')->nullable();
            $table->string('invoice_footer')->nullable();
            $table->timestamps();
        });

        Schema::table('pos_users', function (Blueprint $table) {
            $table->foreignUuid('biller_id')->nullable()->references('id')->on('pos_companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_companies');
    }
};
