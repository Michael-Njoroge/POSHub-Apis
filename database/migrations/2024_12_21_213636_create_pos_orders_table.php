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
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamp('payed_at')->useCurrent();
            $table->decimal('total_price', 10, 2);
            $table->decimal('total_price_after', 10, 2)->nullable();
            $table->enum('order_status', [
                'Ordered',
                'Not Processed',
                'Cash on Delivery',
                'Processing',
                'Dispatched',
                'Cancelled',
                'Delivered'
            ])->default('Ordered');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_orders');
    }
};
