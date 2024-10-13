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
