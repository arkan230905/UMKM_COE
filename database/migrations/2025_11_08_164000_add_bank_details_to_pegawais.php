<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pegawais', function (Blueprint $table) {
            // Tambahkan kolom bank jika belum ada
            if (!Schema::hasColumn('pegawais', 'bank')) {
                $table->string('bank', 100)->nullable()->after('tanggal_masuk');
            }
            
            // Tambahkan kolom nomor_rekening jika belum ada
            if (!Schema::hasColumn('pegawais', 'nomor_rekening')) {
                $table->string('nomor_rekening', 50)->nullable()->after('bank');
            }
            
            // Tambahkan kolom nama_rekening jika belum ada
            if (!Schema::hasColumn('pegawais', 'nama_rekening')) {
                $table->string('nama_rekening', 100)->nullable()->after('nomor_rekening');
            }
            
            // Tambahkan kolom kode_pegawai jika belum ada
            if (!Schema::hasColumn('pegawais', 'kode_pegawai')) {
                $table->string('kode_pegawai', 20)->nullable()->after('id');
            }
        });
        
        // Generate kode_pegawai untuk data yang sudah ada
        $pegawais = DB::table('pegawais')->whereNull('kode_pegawai')->get();
        
        foreach ($pegawais as $index => $pegawai) {
            $kode = 'PGW' . str_pad($pegawai->id, 4, '0', STR_PAD_LEFT);
            DB::table('pegawais')
                ->where('id', $pegawai->id)
                ->update(['kode_pegawai' => $kode]);
        }
        
        // Set kolom kode_pegawai menjadi NOT NULL
        Schema::table('pegawais', function (Blueprint $table) {
            $table->string('kode_pegawai', 20)->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('pegawais', function (Blueprint $table) {
            // Jangan hapus kolom untuk menghindari kehilangan data
            // Jika ingin rollback, buat migrasi terpisah
        });
    }
};
