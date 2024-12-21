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
        Schema::create('pos_cart_product', function (Blueprint $table) {
            $table->foreignUuid('cart_id')->constrained('pos_carts')->onDelete('cascade');
            $table->foreignUuid('product_id')->constrained('pos_products')->onDelete('cascade');
            $table->foreignUuid('color')->constrained('pos_colors')->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('price', 8, 2);
            $table->timestamps();

            $table->unique(['cart_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_cart_product');
    }
};
