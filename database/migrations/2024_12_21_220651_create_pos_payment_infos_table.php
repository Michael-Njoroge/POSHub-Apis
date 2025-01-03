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
        Schema::create('pos_payment_infos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('payment_method');
            $table->string('transaction_id');
            $table->string('payment_description');
            $table->string('amount');
            $table->timestamps();
        });

        Schema::table('pos_orders',function (Blueprint $table) {
            $table->foreignUuid('payment_info_id')->constrained('pos_payment_infos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_payment_infos');
    }
};
