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
        Schema::create('pos_products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('code');
            $table->string('unit');
            $table->string('cost') ;
            $table->string('price');
            $table->string('alert_quantity')->nullable();
            $table->string('quantity')->nullable();
            $table->boolean('track_quantity')->default(1);
            $table->string('barcode_symbol')->nullable();
            $table->string('type')->nullable();
            $table->string('author')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_archived')->default(0);
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('pos_product_warehouse', function (Blueprint $table) {
            $table->foreignUuid('product_id')->constrained('pos_products')->onDelete('cascade');
            $table->foreignUuid('warehouse_id')->constrained('pos_warehouses')->onDelete('cascade');
            $table->integer('quantity')->default(0); 
            $table->timestamps();
            $table->primary(['product_id', 'warehouse_id']); 
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


Schema::create('products', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->string('title');
    $table->string('slug')->unique();
    $table->text('description');
    $table->decimal('price', 8, 2);
    $table->foreignUuid('brand')->constrained('brands')->onDelete('cascade');
    $table->foreignUuid('category')->constrained('product_categories')->onDelete('cascade');
    $table->integer('quantity');
    $table->integer('sold')->default(0);
    $table->decimal('total_ratings', 8, 2)->default(0);
    $table->json('color')->nullable();
    $table->string('tags');
    $table->timestamps();
});

Schema::create('user_products', function (Blueprint $table) {
    $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
    $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
    $table->timestamps();

    $table->unique(['user_id', 'product_id']);
});

Schema::create('ratings', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignUuid('product_id')->constrained('products')->onDelete('cascade');
    $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
    $table->integer('star')->nullable(false);
    $table->text('comment')->nullable();
    $table->timestamps();
});