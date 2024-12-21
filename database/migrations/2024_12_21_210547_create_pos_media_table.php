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
        Schema::create('pos_media', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('medially_id')->nullable();  
            $table->string('medially_type')->nullable(); 
            $table->text('file_url');
            $table->text('asset_id');
            $table->text('public_id');
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->unsignedBigInteger('size');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_media');
    }
};
