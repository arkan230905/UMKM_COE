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
        if (!Schema::hasTable('reviews')) {
            return;
        }

        Schema::table('reviews', function (Blueprint $table) {
            // Check if the unique constraint exists before trying to drop it
            $uniqueExists = \DB::select("SELECT COUNT(*) as count FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'reviews' AND index_name = 'reviews_order_id_user_id_unique'");
            if ($uniqueExists[0]->count > 0) {
                // Drop foreign key on order_id if it exists (needed before dropping the unique index)
                try {
                    $table->dropForeign(['order_id']);
                } catch (\Exception $e) {
                    // Ignore if foreign key doesn't exist
                }

                // Drop the unique constraint
                $table->dropUnique(['order_id', 'user_id']);
            }

            // Check if produk_id column already exists
            if (!Schema::hasColumn('reviews', 'produk_id')) {
                $table->unsignedBigInteger('produk_id')->after('order_id')->nullable();
                $table->foreign('produk_id')->references('id')->on('produks')->onDelete('cascade');
            }

            // Re-add foreign key on order_id if it was dropped
            try {
                $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            } catch (\Exception $e) {
                // Ignore if orders table doesn't exist or foreign key fails
            }

            // Check if the new unique constraint doesn't exist before adding it
            $newUniqueExists = \DB::select("SELECT COUNT(*) as count FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'reviews' AND index_name = 'reviews_order_id_user_id_produk_id_unique'");
            if ($newUniqueExists[0]->count == 0) {
                $table->unique(['order_id', 'user_id', 'produk_id']);
            }
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
