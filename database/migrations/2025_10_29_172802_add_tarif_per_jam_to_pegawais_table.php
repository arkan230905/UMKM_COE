<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawais', 'tarif_per_jam')) {
                $table->decimal('tarif_per_jam', 15, 2)->nullable()->after('gaji_pokok');
            }
            if (!Schema::hasColumn('pegawais', 'jam_kerja_per_minggu')) {
                $table->integer('jam_kerja_per_minggu')->default(40)->after('tarif_per_jam');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (Schema::hasColumn('pegawais', 'tarif_per_jam')) {
                $table->dropColumn('tarif_per_jam');
            }
            if (Schema::hasColumn('pegawais', 'jam_kerja_per_minggu')) {
                $table->dropColumn('jam_kerja_per_minggu');
            }
        });
    }
};
