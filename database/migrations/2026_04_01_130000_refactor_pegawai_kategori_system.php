<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create kategori_pegawai table
        Schema::create('kategori_pegawai', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->unique(); // BTKL, BTKTL
            $table->string('deskripsi')->nullable();
            $table->timestamps();
        });

        // Insert default categories
        DB::table('kategori_pegawai')->insert([
            ['nama' => 'BTKL', 'deskripsi' => 'Tenaga Kerja Langsung', 'created_at' => now(), 'updated_at' => now()],
            ['nama' => 'BTKTL', 'deskripsi' => 'Bukan Tenaga Kerja Langsung', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Add kategori_id to jabatans table
        Schema::table('jabatans', function (Blueprint $table) {
            $table->foreignId('kategori_id')->nullable()->after('id')->constrained('kategori_pegawai');
        });

        // Add kategori_id to pegawais table
        Schema::table('pegawais', function (Blueprint $table) {
            $table->foreignId('kategori_id')->nullable()->after('jabatan')->constrained('kategori_pegawai');
        });

        // Backfill jabatans.kategori_id based on existing kategori field
        DB::statement('
            UPDATE jabatans j 
            SET kategori_id = (
                CASE 
                    WHEN LOWER(j.kategori) LIKE \'%btkl%\' THEN (SELECT id FROM kategori_pegawai WHERE nama = \'BTKL\')
                    WHEN LOWER(j.kategori) LIKE \'%btktl%\' THEN (SELECT id FROM kategori_pegawai WHERE nama = \'BTKTL\')
                    ELSE NULL
                END
            )
        ');

        // Backfill pegawais.kategori_id based on existing jenis_pegawai field
        DB::statement('
            UPDATE pegawais p 
            SET kategori_id = (
                CASE 
                    WHEN LOWER(p.jenis_pegawai) = \'btkl\' THEN (SELECT id FROM kategori_pegawai WHERE nama = \'BTKL\')
                    WHEN LOWER(p.jenis_pegawai) = \'btktl\' THEN (SELECT id FROM kategori_pegawai WHERE nama = \'BTKTL\')
                    ELSE NULL
                END
            )
        ');

        // Update jabatans table to use proper field names
        Schema::table('jabatans', function (Blueprint $table) {
            if (!Schema::hasColumn('jabatans', 'tarif_per_jam')) {
                $table->renameColumn('tarif', 'tarif_per_jam');
            }
            if (!Schema::hasColumn('jabatans', 'gaji_pokok')) {
                $table->renameColumn('gaji', 'gaji_pokok');
            }
        });
    }

    public function down()
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropColumn('kategori_id');
        });

        Schema::table('jabatans', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropColumn('kategori_id');
        });

        Schema::dropIfExists('kategori_pegawai');
    }
};
