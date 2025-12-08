<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom untuk menyimpan total per komponen biaya
     */
    public function up(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            // Tambah kolom total_bbb jika belum ada
            if (!Schema::hasColumn('boms', 'total_bbb')) {
                $table->decimal('total_bbb', 15, 2)->default(0)->after('total_biaya')->comment('Total Biaya Bahan Baku');
            }
            
            // Tambah kolom total_hpp jika belum ada
            if (!Schema::hasColumn('boms', 'total_hpp')) {
                $table->decimal('total_hpp', 15, 2)->default(0)->after('total_bop')->comment('Total Harga Pokok Produksi');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boms', function (Blueprint $table) {
            if (Schema::hasColumn('boms', 'total_bbb')) {
                $table->dropColumn('total_bbb');
            }
            if (Schema::hasColumn('boms', 'total_hpp')) {
                $table->dropColumn('total_hpp');
            }
        });
    }
};
