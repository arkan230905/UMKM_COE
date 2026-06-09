<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tambah kategori "Mesin" ke Aset Tetap
     */
    public function up(): void
    {
        // Get Aset Tetap jenis_aset_id
        $tetapId = DB::table('jenis_asets')->where('nama', 'Aset Tetap')->value('id');
        
        if ($tetapId) {
            // Check if Mesin already exists
            $exists = DB::table('kategori_asets')
                ->where('kode', 'AT-06')
                ->orWhere('nama', 'Mesin')
                ->exists();
            
            if (!$exists) {
                DB::table('kategori_asets')->insert([
                    'jenis_aset_id' => $tetapId,
                    'kode' => 'AT-06',
                    'nama' => 'Mesin',
                    'deskripsi' => 'Mesin produksi dan operasional',
                    'umur_ekonomis' => 10,
                    'tarif_penyusutan' => 10.00, // 100% / 10 tahun
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                \Log::info('✅ Kategori aset "Mesin" berhasil ditambahkan');
            } else {
                \Log::info('ℹ️  Kategori aset "Mesin" sudah ada, skip');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('kategori_asets')
            ->where('kode', 'AT-06')
            ->where('nama', 'Mesin')
            ->delete();
    }
};
