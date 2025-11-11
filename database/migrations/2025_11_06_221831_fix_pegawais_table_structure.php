<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan tabel pegawais sudah ada
        if (!Schema::hasTable('pegawais')) return;

        Schema::table('pegawais', function (Blueprint $table) {
            // ✅ Jangan hapus kolom id, cukup pastikan ada
            if (!Schema::hasColumn('pegawais', 'id')) {
                $table->id()->first();
            }

            // ✅ Tambahkan kolom jika belum ada
            if (!Schema::hasColumn('pegawais', 'kode_pegawai')) {
                $table->string('kode_pegawai', 20)->nullable()->after('id');
            }

            if (!Schema::hasColumn('pegawais', 'no_telepon')) {
                $table->string('no_telepon', 20)->after('email');
            }

            if (!Schema::hasColumn('pegawais', 'nama_bank')) {
                $table->string('nama_bank', 100)->nullable()->after('alamat');
            }

            if (!Schema::hasColumn('pegawais', 'no_rekening')) {
                $table->string('no_rekening', 50)->nullable()->after('nama_bank');
            }

            if (!Schema::hasColumn('pegawais', 'kategori')) {
                $table->enum('kategori', ['BTKL', 'BTKTL'])->default('BTKL')->after('jabatan');
            }

            if (!Schema::hasColumn('pegawais', 'asuransi')) {
                $table->decimal('asuransi', 15, 2)->default(0)->after('kategori');
            }

            if (!Schema::hasColumn('pegawais', 'tarif')) {
                $table->decimal('tarif', 15, 2)->default(0)->after('asuransi');
            }

            if (!Schema::hasColumn('pegawais', 'tunjangan')) {
                $table->decimal('tunjangan', 15, 2)->default(0)->after('tarif');
            }
        });

        // ✅ Cek apakah sudah ada unique index untuk kode_pegawai
        $indexExists = DB::select("
            SELECT COUNT(1) AS total
            FROM information_schema.statistics 
            WHERE table_schema = DATABASE()
              AND table_name = 'pegawais'
              AND column_name = 'kode_pegawai'
        ");

        if (empty($indexExists) || $indexExists[0]->total == 0) {
            Schema::table('pegawais', function (Blueprint $table) {
                $table->unique('kode_pegawai');
            });
        }
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (Schema::hasColumn('pegawais', 'kode_pegawai')) {
                $table->dropUnique(['kode_pegawai']);
            }
        });
    }
};
