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
        Schema::table('pos_products', function (Blueprint $table) {
            $table->string('slug')->unique();
            $table->integer('sold')->default(0);
            $table->decimal('total_ratings', 8, 2)->default(0);
            $table->json('color')->nullable();
            $table->string('tags');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_products');
    }
};
