<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Dapatkan daftar kolom yang ada di tabel pegawais
        $columns = Schema::getColumnListing('pegawais');
        
        // Tambahkan kolom-kolom yang diperlukan
        Schema::table('pegawais', function (Blueprint $table) use ($columns) {
            // Tentukan kolom setelah mana kita akan menambahkan kolom baru
            $afterColumn = 'jenis_pegawai';
            if (!in_array('jenis_pegawai', $columns)) {
                $afterColumn = 'jabatan';
            }
            if (!in_array($afterColumn, $columns)) {
                $afterColumn = 'jenis_kelamin';
            }
            
            // Tambahkan kolom bank jika belum ada
            if (!in_array('bank', $columns)) {
                $table->string('bank', 100)->nullable()->after($afterColumn);
            }
            
            // Tambahkan kolom nomor_rekening jika belum ada
            if (!in_array('nomor_rekening', $columns)) {
                $table->string('nomor_rekening', 50)->nullable()->after('bank');
            }
            
            // Tambahkan kolom nama_rekening jika belum ada
            if (!in_array('nama_rekening', $columns)) {
                $table->string('nama_rekening', 100)->nullable()->after('nomor_rekening');
            }
            
            // Tambahkan kolom kode_pegawai jika belum ada
            if (!in_array('kode_pegawai', $columns)) {
                $table->string('kode_pegawai', 20)->nullable()->after('id');
            }
        });
        
        // Generate kode_pegawai untuk data yang sudah ada
        if (Schema::hasColumn('pegawais', 'kode_pegawai')) {
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
    }

    public function down()
    {
        // Jangan hapus kolom untuk menghindari kehilangan data
        // Jika ingin rollback, buat migrasi terpisah
    }
};
