<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('pegawais', 'nomor_induk_pegawai')) {
                $table->string('nomor_induk_pegawai')->nullable()->after('id');
            }
            if (!Schema::hasColumn('pegawais', 'jenis_kelamin')) {
                $table->string('jenis_kelamin', 1)->nullable()->after('alamat');
            }
            if (!Schema::hasColumn('pegawais', 'kategori_tenaga_kerja')) {
                $table->string('kategori_tenaga_kerja', 10)->nullable()->after('jabatan');
            }
            if (!Schema::hasColumn('pegawais', 'tanggal_masuk')) {
                $table->date('tanggal_masuk')->nullable()->after('kategori_tenaga_kerja');
            }
            if (!Schema::hasColumn('pegawais', 'status_aktif')) {
                $table->boolean('status_aktif')->default(true)->after('tanggal_masuk');
            }
            if (!Schema::hasColumn('pegawais', 'gaji_pokok')) {
                $table->decimal('gaji_pokok', 15, 2)->default(0)->after('status_aktif');
            }
            if (!Schema::hasColumn('pegawais', 'tarif_per_jam')) {
                $table->decimal('tarif_per_jam', 15, 2)->default(0)->after('gaji_pokok');
            }
            if (!Schema::hasColumn('pegawais', 'gaji')) {
                $table->decimal('gaji', 15, 2)->default(0)->after('tarif_per_jam');
            }
            if (!Schema::hasColumn('pegawais', 'tunjangan')) {
                $table->decimal('tunjangan', 15, 2)->default(0)->after('gaji');
            }
        });

        // Create index on nomor_induk_pegawai if exists (not unique to avoid conflicts)
        Schema::table('pegawais', function (Blueprint $table) {
            if (Schema::hasColumn('pegawais', 'nomor_induk_pegawai')) {
                // Use index instead of unique to avoid duplicate value errors
                $table->index('nomor_induk_pegawai', 'idx_pegawais_nip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $drops = [
                'nomor_induk_pegawai','jenis_kelamin','kategori_tenaga_kerja','tanggal_masuk','status_aktif','gaji_pokok','tarif_per_jam','gaji','tunjangan'
            ];
            foreach ($drops as $col) {
                if (Schema::hasColumn('pegawais', $col)) {
                    // For SQLite, drop column support is limited; migrations may need rebuild. We attempt drop where possible.
                    try { $table->dropColumn($col); } catch (\Throwable $e) {}
                }
            }
            // drop unique if exists
            try { $table->dropUnique('uniq_pegawais_nip'); } catch (\Throwable $e) {}
        });
    }
};
