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
        Schema::table('pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawais', 'kode_pegawai')) {
                $table->string('kode_pegawai', 20)->after('id')->unique();
            }
        });
        
        // Generate kode_pegawai untuk data yang sudah ada
        $pegawais = \DB::table('pegawais')->get();
        foreach ($pegawais as $index => $pegawai) {
            $kode = 'PGW' . str_pad($pegawai->id, 4, '0', STR_PAD_LEFT);
            \DB::table('pegawais')
                ->where('id', $pegawai->id)
                ->update(['kode_pegawai' => $kode]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (Schema::hasColumn('pegawais', 'kode_pegawai')) {
                $table->dropColumn('kode_pegawai');
            }
        });
    }
};
