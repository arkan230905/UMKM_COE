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
        if (!Schema::hasTable('pegawais')) {
            return;
        }

        if (Schema::hasColumn('pegawais', 'kode_pegawai')) {
            return;
        }

        Schema::table('pegawais', function (Blueprint $table) {
            $table->string('kode_pegawai', 10)->nullable()->after('id')->unique();
        });

        // Update existing records with default kode_pegawai
        try {
            $pegawais = DB::table('pegawais')->get();
            foreach ($pegawais as $index => $pegawai) {
                $kode = 'PGW' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                DB::table('pegawais')
                    ->where('id', $pegawai->id)
                    ->update(['kode_pegawai' => $kode]);
            }
        } catch (\Exception $e) {
            // Log error but continue
        }
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
