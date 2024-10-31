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
        Schema::table('pos_users', function (Blueprint $table) {
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('otp_verified_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_users', function (Blueprint $table) {
            $table->dropColumn('otp_expires_at');
            $table->dropColumn('otp_verified_at');
            $table->dropColumn('otp');
            $table->dropSoftDeletes();
        });
    }
};
