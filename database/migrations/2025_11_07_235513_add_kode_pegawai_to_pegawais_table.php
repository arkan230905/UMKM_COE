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
        Schema::table('pegawais', function (Blueprint $table) {
            $table->string('kode_pegawai', 10)->after('id')->unique();
        });

        // Update existing records with default kode_pegawai
        $pegawais = DB::table('pegawais')->get();
        foreach ($pegawais as $index => $pegawai) {
            $kode = 'PGW' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
            DB::table('pegawais')
                ->where('id', $pegawai->id)
                ->update(['kode_pegawai' => $kode]);
        }

        // Make the column not nullable after populating
        Schema::table('pegawais', function (Blueprint $table) {
            $table->string('kode_pegawai', 10)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn('kode_pegawai');
        });
    }
};
