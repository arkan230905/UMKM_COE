<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Pegawai;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('pegawais', 'kode_pegawai')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->string('kode_pegawai', 20)->unique()->nullable()->after('id');
            });

            // Generate kode_pegawai for existing records
            $pegawais = Pegawai::all();
            foreach ($pegawais as $index => $pegawai) {
                $pegawai->kode_pegawai = 'PGW' . str_pad($pegawai->id, 4, '0', STR_PAD_LEFT);
                $pegawai->save();
            }

            // Make the column not nullable after populating
            Schema::table('pegawais', function (Blueprint $table) {
                $table->string('kode_pegawai', 20)->unique()->change();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('pegawais', 'kode_pegawai')) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->dropColumn('kode_pegawai');
            });
        }
    }
};
