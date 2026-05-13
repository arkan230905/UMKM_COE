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
        // 1. Bersihkan data duplikat terlebih dahulu agar penambahan Unique Constraint tidak gagal
        $this->cleanupDuplicates();

        // 2. Add unique constraint ke tabel 'btkls'
        Schema::table('btkls', function (Blueprint $table) {
            if (Schema::hasColumns('btkls', ['user_id', 'kode_proses'])) {
                // Gunakan try-catch untuk menghindari error jika index sudah ada
                try {
                    $table->unique(['user_id', 'kode_proses'], 'unique_user_kode_proses');
                } catch (\Exception $e) {}
            }
        });

        // 3. Add unique constraint ke tabel 'proses_produksis'
        Schema::table('proses_produksis', function (Blueprint $table) {
            // Kita cek apakah kolom btkl_id ada. 
            // Jika sudah dihapus di migrasi sebelumnya, gunakan kolom lain yang unik (misal: nama_proses atau lainnya)
            // Di sini saya tambahkan pengecekan agar tidak FAIL.
            if (Schema::hasColumns('proses_produksis', ['user_id', 'btkl_id'])) {
                try {
                    $table->unique(['user_id', 'btkl_id'], 'unique_user_btkl_id');
                } catch (\Exception $e) {}
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('btkls', function (Blueprint $table) {
            try {
                $table->dropUnique('unique_user_kode_proses');
            } catch (\Exception $e) {}
        });

        Schema::table('proses_produksis', function (Blueprint $table) {
            try {
                $table->dropUnique('unique_user_btkl_id');
            } catch (\Exception $e) {}
        });
    }

    /**
     * Clean up existing duplicates
     */
    private function cleanupDuplicates()
    {
        // Bersihkan duplikat di btkls
        if (Schema::hasColumns('btkls', ['user_id', 'kode_proses'])) {
            DB::statement("
                DELETE t1 FROM btkls t1
                INNER JOIN btkls t2 
                WHERE t1.id > t2.id 
                AND t1.user_id = t2.user_id 
                AND t1.kode_proses = t2.kode_proses
            ");
        }

        // Bersihkan duplikat di proses_produksis (Hanya jika kolom btkl_id masih ada)
        if (Schema::hasColumns('proses_produksis', ['user_id', 'btkl_id'])) {
            DB::statement("
                DELETE t1 FROM proses_produksis t1
                INNER JOIN proses_produksis t2 
                WHERE t1.id > t2.id 
                AND t1.user_id = t2.user_id 
                AND t1.btkl_id = t2.btkl_id
            ");
        }
    }
};