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
        Schema::create('pos_product_color', function (Blueprint $table) {
            $table->foreignUuid('products_id')->constrained('pos_products')->onDelete('cascade');
            $table->foreignUuid('color_id')->constrained('pos_colors')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['products_id', 'color_id']);
        });

        Schema::table('pos_products', function (Blueprint $table) {
            $table->dropColumn('color'); 
            $table->dropForeign(['brand']); 
            $table->dropColumn('brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_product_color');

        Schema::table('pos_products', function (Blueprint $table) {
            $table->string('color')->nullable();
            $table->string('brand')->nullable(false)->change();
        });
    }
};
