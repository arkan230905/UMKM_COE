<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if table exists first
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                // Add produk_id if it doesn't exist
                if (!Schema::hasColumn('reviews', 'produk_id')) {
                    $table->unsignedBigInteger('produk_id')->nullable()->after('user_id');
                    $table->foreign('produk_id')->references('id')->on('produks')->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('reviews')) {
            Schema::table('reviews', function (Blueprint $table) {
                if (Schema::hasColumn('reviews', 'produk_id')) {
                    $table->dropForeign(['produk_id']);
                    $table->dropColumn('produk_id');
                }
            });
        }
    }
};
