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
        Schema::create('pos_blog_dislikes', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained('pos_users')->onDelete('cascade');
            $table->foreignUuid('blog_id')->constrained('pos_blogs')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'blog_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_blog_dislikes');
    }
};
