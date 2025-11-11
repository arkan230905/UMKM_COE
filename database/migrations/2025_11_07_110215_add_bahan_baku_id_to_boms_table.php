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
        Schema::table('boms', function (Blueprint $table) {
            // Tambahkan kolom bahan_baku_id sebagai foreign key
            $table->unsignedBigInteger('bahan_baku_id')->after('produk_id');

            // Opsional: jika ingin pakai foreign key constraint
            $table->foreign('bahan_baku_id')->references('id')->on('bahan_bakus')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            // Hapus foreign key dulu
            $table->dropForeign(['bahan_baku_id']);
            // Hapus kolom
            $table->dropColumn('bahan_baku_id');
        });
    }
};
