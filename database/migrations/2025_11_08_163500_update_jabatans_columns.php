<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jabatans', function (Blueprint $table) {
            // Ubah nama kolom gaji_pokok menjadi gaji
            if (Schema::hasColumn('jabatans', 'gaji_pokok') && !Schema::hasColumn('jabatans', 'gaji')) {
                $table->renameColumn('gaji_pokok', 'gaji');
            } elseif (!Schema::hasColumn('jabatans', 'gaji')) {
                $table->decimal('gaji', 15, 2)->default(0)->after('asuransi');
            }
            
            // Ubah nama kolom tarif_lembur menjadi tarif
            if (Schema::hasColumn('jabatans', 'tarif_lembur') && !Schema::hasColumn('jabatans', 'tarif')) {
                $table->renameColumn('tarif_lembur', 'tarif');
            } elseif (!Schema::hasColumn('jabatans', 'tarif')) {
                $table->decimal('tarif', 15, 2)->default(0)->after('gaji');
            }
            
            // Jika kolom gaji_pokok masih ada, hapus
            if (Schema::hasColumn('jabatans', 'gaji_pokok')) {
                $table->dropColumn('gaji_pokok');
            }
            
            // Jika kolom tarif_lembur masih ada, hapus
            if (Schema::hasColumn('jabatans', 'tarif_lembur')) {
                $table->dropColumn('tarif_lembur');
            }
        });
    }

    public function down()
    {
        Schema::table('jabatans', function (Blueprint $table) {
            // Kembalikan ke nama kolom semula jika rollback
            if (Schema::hasColumn('jabatans', 'gaji') && !Schema::hasColumn('jabatans', 'gaji_pokok')) {
                $table->renameColumn('gaji', 'gaji_pokok');
            }
            
            if (Schema::hasColumn('jabatans', 'tarif') && !Schema::hasColumn('jabatans', 'tarif_lembur')) {
                $table->renameColumn('tarif', 'tarif_lembur');
            }
        });
    }
};
