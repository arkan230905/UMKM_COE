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
            // Drop foreign key on order_id if it exists (needed before dropping the unique index)
            try {
                $table->dropForeign(['order_id']);
            } catch (\Exception $e) {
                // Ignore if foreign key doesn't exist
            }

            // Drop the unique constraint
            try {
                $table->dropUnique(['order_id', 'user_id']);
            } catch (\Exception $e) {
                // Ignore if unique constraint doesn't exist
            }

            $table->unsignedBigInteger('produk_id')->after('order_id')->nullable();

            $table->foreign('produk_id')->references('id')->on('produks')->onDelete('cascade');

            // Re-add foreign key on order_id if it was dropped
            try {
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            } catch (\Exception $e) {
                // Ignore if orders table doesn't exist or foreign key fails
            }

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
