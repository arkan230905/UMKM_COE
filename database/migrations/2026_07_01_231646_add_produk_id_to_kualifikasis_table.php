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
        Schema::table('kualifikasis', function (Blueprint $table) {
            // Nullable agar data lama tidak rusak; wajib diisi di validasi form untuk record baru
            $table->unsignedBigInteger('produk_id')->nullable()->after('user_id');
            $table->foreign('produk_id')->references('id')->on('produks')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kualifikasis', function (Blueprint $table) {
            $table->dropForeign(['produk_id']);
            $table->dropColumn('produk_id');
        });
    }
};
