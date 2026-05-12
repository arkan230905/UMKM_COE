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
        // Check if the table exists
        if (Schema::hasTable('presensis')) {
            // Add missing columns if they don't exist
            Schema::table('presensis', function (Blueprint $table) {
                if (!Schema::hasColumn('presensis', 'tgl_presensi')) {
                    $table->date('tgl_presensi')->after('pegawai_id');
                }
                if (!Schema::hasColumn('presensis', 'jam_masuk')) {
                    $table->time('jam_masuk')->after('tgl_presensi');
                }
                if (!Schema::hasColumn('presensis', 'jam_keluar')) {
                    $table->time('jam_keluar')->nullable()->after('jam_masuk');
                }
                if (!Schema::hasColumn('presensis', 'status')) {
                    $table->enum('status', ['Hadir', 'Absen', 'Izin', 'Sakit'])->after('jam_keluar');
                }
                if (!Schema::hasColumn('presensis', 'jumlah_jam')) {
                    $table->decimal('jumlah_jam', 8, 2)->nullable()->after('status');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a non-destructive migration, so no need to do anything in down
    }
};
