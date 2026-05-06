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
        // 1. Enhance presensis table
        if (Schema::hasTable('presensis')) {
            Schema::table('presensis', function (Blueprint $table) {
                // Add periode fields if not exists
                if (!Schema::hasColumn('presensis', 'periode_bulan')) {
                    $table->tinyInteger('periode_bulan')->nullable()->after('tgl_presensi')->comment('1-12 untuk Januari-Desember');
                }
                if (!Schema::hasColumn('presensis', 'periode_tahun')) {
                    $table->year('periode_tahun')->nullable()->after('periode_bulan');
                }
                
                // Ensure jumlah_jam is decimal for precision
                if (Schema::hasColumn('presensis', 'jumlah_jam')) {
                    $table->decimal('jumlah_jam', 5, 2)->default(0)->change();
                }
                
                // Add index for faster queries
                $table->index(['pegawai_id', 'periode_bulan', 'periode_tahun'], 'idx_pegawai_periode');
                $table->index(['tgl_presensi'], 'idx_tgl_presensi');
            });
        }

        // 2. Enhance penggajians table
        if (Schema::hasTable('penggajians')) {
            Schema::table('penggajians', function (Blueprint $table) {
                // Add periode fields if not exists
                if (!Schema::hasColumn('penggajians', 'periode_bulan')) {
                    $table->tinyInteger('periode_bulan')->nullable()->after('pegawai_id')->comment('1-12 untuk Januari-Desember');
                }
                if (!Schema::hasColumn('penggajians', 'periode_tahun')) {
                    $table->year('periode_tahun')->nullable()->after('periode_bulan');
                }
                
                // Add attendance summary fields
                if (!Schema::hasColumn('penggajians', 'total_hari_hadir')) {
                    $table->integer('total_hari_hadir')->default(0)->after('periode_tahun')->comment('Total hari hadir dalam bulan');
                }
                if (!Schema::hasColumn('penggajians', 'total_alpha')) {
                    $table->integer('total_alpha')->default(0)->after('total_hari_hadir')->comment('Total hari alpha dalam bulan');
                }
                if (!Schema::hasColumn('penggajians', 'total_jam')) {
                    $table->decimal('total_jam', 8, 2)->default(0)->after('total_alpha')->comment('Total jam kerja aktual dalam bulan');
                }
                
                // Ensure total_jam_kerja is decimal
                if (Schema::hasColumn('penggajians', 'total_jam_kerja')) {
                    $table->decimal('total_jam_kerja', 8, 2)->default(0)->change();
                }
                
                // Add unique constraint to prevent duplicate payroll
                $table->unique(['pegawai_id', 'periode_bulan', 'periode_tahun'], 'unique_payroll_periode');
            });
        }

        // 3. Create kalender_kerja table for monthly work calendar
        if (!Schema::hasTable('kalender_kerja')) {
            Schema::create('kalender_kerja', function (Blueprint $table) {
                $table->id();
                $table->tinyInteger('bulan')->comment('1-12 untuk Januari-Desember');
                $table->year('tahun');
                $table->integer('target_hari_kerja')->default(26)->comment('Target hari kerja dalam bulan ini');
                $table->text('keterangan')->nullable();
                $table->timestamps();
                
                $table->unique(['bulan', 'tahun'], 'unique_kalender_bulan');
            });
        }

        // 4. Create rekap_presensi_bulanan table for monthly attendance summary
        if (!Schema::hasTable('rekap_presensi_bulanan')) {
            Schema::create('rekap_presensi_bulanan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pegawai_id')->constrained('pegawais')->onDelete('cascade');
                $table->tinyInteger('periode_bulan')->comment('1-12 untuk Januari-Desember');
                $table->year('periode_tahun');
                $table->integer('total_hari_hadir')->default(0);
                $table->integer('total_alpha')->default(0);
                $table->integer('total_masuk_saja')->default(0)->comment('Hanya absen masuk, tidak keluar');
                $table->decimal('total_jam_bulanan', 8, 2)->default(0)->comment('Total jam kerja aktual');
                $table->integer('target_hari_kerja')->default(26);
                $table->decimal('persentase_kehadiran', 5, 2)->default(0)->comment('Persentase kehadiran');
                $table->decimal('estimasi_gaji', 12, 2)->default(0)->comment('Estimasi gaji berdasarkan jam kerja');
                $table->timestamps();
                
                $table->unique(['pegawai_id', 'periode_bulan', 'periode_tahun'], 'unique_rekap_periode');
                $table->index(['periode_bulan', 'periode_tahun'], 'idx_periode');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('rekap_presensi_bulanan');
        Schema::dropIfExists('kalender_kerja');
        
        // Remove added columns from penggajians
        if (Schema::hasTable('penggajians')) {
            Schema::table('penggajians', function (Blueprint $table) {
                $table->dropUnique('unique_payroll_periode');
                $table->dropColumn(['periode_bulan', 'periode_tahun', 'total_hari_hadir', 'total_alpha', 'total_jam']);
            });
        }
        
        // Remove added columns from presensis
        if (Schema::hasTable('presensis')) {
            Schema::table('presensis', function (Blueprint $table) {
                $table->dropIndex('idx_pegawai_periode');
                $table->dropIndex('idx_tgl_presensi');
                $table->dropColumn(['periode_bulan', 'periode_tahun']);
            });
        }
    }
};
