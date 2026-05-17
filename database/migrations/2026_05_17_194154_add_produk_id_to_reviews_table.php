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
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'user_id']);
            $table->unsignedBigInteger('produk_id')->after('order_id')->nullable();
            
            $table->foreign('produk_id')->references('id')->on('produks')->onDelete('cascade');
            
            // Re-add unique constraint including produk_id
            $table->unique(['order_id', 'user_id', 'produk_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'user_id', 'produk_id']);
            $table->dropForeign(['produk_id']);
            $table->dropColumn('produk_id');
            
            $table->unique(['order_id', 'user_id']);
        });
    }
};
