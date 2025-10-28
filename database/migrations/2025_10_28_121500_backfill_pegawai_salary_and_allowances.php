<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Sinkronisasi jenis_pegawai dari kategori_tenaga_kerja (BTKL/BTKTL -> lowercase)
        DB::table('pegawais')
            ->whereNotNull('kategori_tenaga_kerja')
            ->update([
                'jenis_pegawai' => DB::raw("LOWER(kategori_tenaga_kerja)")
            ]);

        // 2) Backfill gaji_pokok dari gaji jika gaji_pokok masih 0 atau NULL
        DB::table('pegawais')
            ->where(function($q){
                $q->whereNull('gaji_pokok')->orWhere('gaji_pokok', 0);
            })
            ->whereNotNull('gaji')
            ->update([
                'gaji_pokok' => DB::raw('gaji')
            ]);

        // 3) Set tunjangan default berdasarkan jabatan (bisa diubah sesuai kebijakan)
        //    Manager: 1.000.000; Staff: 500.000; Kasir: 300.000; lainnya: 0
        DB::table('pegawais')->where('jabatan', 'Manager')->update(['tunjangan' => 1000000]);
        DB::table('pegawais')->where('jabatan', 'Staff')->update(['tunjangan' => 500000]);
        DB::table('pegawais')->where('jabatan', 'Kasir')->update(['tunjangan' => 300000]);
        DB::table('pegawais')
            ->whereNotIn('jabatan', ['Manager','Staff','Kasir'])
            ->update(['tunjangan' => DB::raw('COALESCE(tunjangan, 0)')]);
    }

    public function down(): void
    {
        // Tidak membalikkan perubahan data (no-op)
    }
};
