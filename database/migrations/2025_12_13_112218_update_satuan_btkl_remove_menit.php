<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Update existing data that uses 'menit' satuan to 'jam'
     */
    public function up(): void
    {
        // Update proses_produksis table - convert menit to jam
        DB::table('proses_produksis')
            ->where('satuan_btkl', 'menit')
            ->update([
                'satuan_btkl' => 'jam',
                'tarif_btkl' => DB::raw('tarif_btkl * 60'), // Convert per-minute rate to per-hour rate
                'updated_at' => now()
            ]);

        // Update bom_proses table - convert menit to jam
        DB::table('bom_proses')
            ->where('satuan_durasi', 'menit')
            ->update([
                'satuan_durasi' => 'jam',
                'durasi' => DB::raw('durasi / 60'), // Convert minutes to hours
                'updated_at' => now()
            ]);

        // Delete 'Menit' satuan from satuans table if exists
        DB::table('satuans')
            ->where('kode', 'MNT')
            ->orWhere('nama', 'Menit')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add Menit satuan
        DB::table('satuans')->insert([
            'kode' => 'MNT',
            'nama' => 'Menit',
            'kategori' => 'waktu',
            'faktor_ke_dasar' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Note: We don't reverse the data conversion as it would be complex
        // and potentially lose precision
    }
};
