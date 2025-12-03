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
        // Check if the column doesn't exist before adding it
        if (!Schema::hasColumn('pegawais', 'kode_pegawai')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->string('kode_pegawai', 20)->nullable()->after('id');
            });

            // Generate kode_pegawai for existing records
            $pegawais = DB::table('pegawais')->get();
            foreach ($pegawais as $index => $pegawai) {
                $kode = 'PGW' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                DB::table('pegawais')
                    ->where('id', $pegawai->id)
                    ->update(['kode_pegawai' => $kode]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('pegawais', 'kode_pegawai')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->dropColumn('kode_pegawai');
            });
        }
    }
};
