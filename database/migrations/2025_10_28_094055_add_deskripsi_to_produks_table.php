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
        Schema::table('produks', function (Blueprint $table) {
            // Tambahkan kolom deskripsi (nullable)
            $table->text('deskripsi')->nullable()->after('nama_produk');

            // Pastikan kolom harga_jual ada dan bisa null (jika belum ada)
            if (!Schema::hasColumn('produks', 'harga_jual')) {
                $table->decimal('harga_jual', 15, 2)->nullable()->after('deskripsi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn(['deskripsi', 'harga_jual']);
        });
    }
};
