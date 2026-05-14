<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Buat tabel kategori_pegawai jika belum ada
        if (!Schema::hasTable('kategori_pegawai')) {
            Schema::create('kategori_pegawai', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 50)->unique(); // BTKL, BTKTL
                $table->string('deskripsi')->nullable();
                $table->timestamps();
            });

            // Isi data default untuk Manufaktur (Process Costing)
            DB::table('kategori_pegawai')->insert([
                ['nama' => 'BTKL', 'deskripsi' => 'Biaya Tenaga Kerja Langsung (Produksi)', 'created_at' => now(), 'updated_at' => now()],
                ['nama' => 'BTKTL', 'deskripsi' => 'Biaya Tenaga Kerja Tidak Langsung (Admin/Lainnya)', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 2. Tambah kategori_id ke jabatans (Gunakan pengecekan agar tidak error duplicate)
        Schema::table('jabatans', function (Blueprint $table) {
            if (!Schema::hasColumn('jabatans', 'kategori_id')) {
                $table->foreignId('kategori_id')->nullable()->after('id')->constrained('kategori_pegawai');
            }
        });

        // 3. Tambah kategori_id ke pegawais (Gunakan pengecekan)
        Schema::table('pegawais', function (Blueprint $table) {
            if (!Schema::hasColumn('pegawais', 'kategori_id')) {
                // Pastikan 'jabatan' adalah kolom yang sudah ada di Master Pegawai Anda
                $table->foreignId('kategori_id')->nullable()->after('jabatan')->constrained('kategori_pegawai');
            }
        });

        // 4. Sinkronisasi Data (Backfill)
        // Mengisi kategori_id berdasarkan teks yang sudah ada di kolom lama
        DB::statement("
            UPDATE jabatans j 
            SET kategori_id = (
                CASE 
                    WHEN LOWER(j.kategori) LIKE '%btkl%' THEN (SELECT id FROM kategori_pegawai WHERE nama = 'BTKL' LIMIT 1)
                    WHEN LOWER(j.kategori) LIKE '%btktl%' THEN (SELECT id FROM kategori_pegawai WHERE nama = 'BTKTL' LIMIT 1)
                    ELSE NULL
                END
            )
        ");

        DB::statement("
            UPDATE pegawais p 
            SET kategori_id = (
                CASE 
                    WHEN LOWER(p.jenis_pegawai) = 'btkl' THEN (SELECT id FROM kategori_pegawai WHERE nama = 'BTKL' LIMIT 1)
                    WHEN LOWER(p.jenis_pegawai) = 'btktl' THEN (SELECT id FROM kategori_pegawai WHERE nama = 'BTKTL' LIMIT 1)
                    ELSE NULL
                END
            )
        ");
    }

    public function down()
    {
        Schema::table('pegawais', function (Blueprint $table) {
            if (Schema::hasColumn('pegawais', 'kategori_id')) {
                $table->dropForeign(['kategori_id']);
                $table->dropColumn('kategori_id');
            }
        });

        Schema::table('jabatans', function (Blueprint $table) {
            if (Schema::hasColumn('jabatans', 'kategori_id')) {
                $table->dropForeign(['kategori_id']);
                $table->dropColumn('kategori_id');
            }
        });

        Schema::dropIfExists('kategori_pegawai');
    }
};