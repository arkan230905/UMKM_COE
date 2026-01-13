<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Menambahkan kolom id sebagai primary key ke tabel pegawais
     */
    public function up(): void
    {
        // Cek apakah kolom id sudah ada
        if (!Schema::hasColumn('pegawais', 'id')) {
            // Hapus primary key lama jika ada
            try {
                DB::statement('ALTER TABLE pegawais DROP PRIMARY KEY');
            } catch (\Exception $e) {
                // Ignore jika tidak ada primary key
            }
            
            // Tambah kolom id sebagai primary key auto increment
            Schema::table('pegawais', function (Blueprint $table) {
                $table->id()->first();
            });
        }
        
        // Tambah kolom yang mungkin belum ada
        if (!Schema::hasColumn('pegawais', 'kode_pegawai')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->string('kode_pegawai')->nullable()->after('id');
            });
        }
        
        if (!Schema::hasColumn('pegawais', 'bank')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->string('bank')->nullable();
                $table->string('nomor_rekening')->nullable();
                $table->string('nama_rekening')->nullable();
            });
        }
        
        if (!Schema::hasColumn('pegawais', 'asuransi')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->decimal('asuransi', 15, 2)->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu rollback karena ini fix struktur
    }
};
