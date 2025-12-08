<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Cek apakah kolom barcode sudah ada
        if (!Schema::hasColumn('produks', 'barcode')) {
            Schema::table('produks', function (Blueprint $table) {
                $table->string('barcode')->unique()->nullable()->after('nama_produk');
            });

            // Generate barcode untuk produk yang sudah ada
            $produks = DB::table('produks')->whereNull('barcode')->get();
            
            foreach ($produks as $produk) {
                // Generate barcode format: 8992XXXXXXXXX (13 digit EAN-13)
                // 8992 = prefix Indonesia
                $barcode = '8992' . str_pad($produk->id, 9, '0', STR_PAD_LEFT);
                
                DB::table('produks')
                    ->where('id', $produk->id)
                    ->update(['barcode' => $barcode]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('produks', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
};
