<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Buat tabel baru dengan skema yang selaras (pegawai_id bertipe string/NIP)
        if (!Schema::hasTable('presensis_new')) {
            Schema::create('presensis_new', function (Blueprint $table) {
                $table->id();
                $table->string('pegawai_id', 50); // NIP (nomor_induk_pegawai)
                $table->date('tgl_presensi');
                $table->time('jam_masuk')->nullable();
                $table->time('jam_keluar')->nullable();
                $table->string('status', 20); // Hadir/Izin/Sakit/Absen
                $table->decimal('jumlah_jam', 8, 2)->default(0);
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();
            });
        }

        // 2) Migrasi data lama ke tabel baru
        // - Jika kolom lama presensis.pegawai_id adalah numeric dan merefer pegawais.id,
        //   maka konversi ke pegawais.nomor_induk_pegawai.
        // - Jika sudah string/NIP, langsung disalin apa adanya.
        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: gunakan LEFT JOIN sederhana
            DB::statement(
                "INSERT INTO presensis_new (id, pegawai_id, tgl_presensi, jam_masuk, jam_keluar, status, jumlah_jam, keterangan, created_at, updated_at)
                 SELECT p.id,
                        COALESCE(pg.nomor_induk_pegawai, p.pegawai_id) as pegawai_id,
                        p.tgl_presensi,
                        p.jam_masuk,
                        p.jam_keluar,
                        CASE WHEN p.status = 'Alpa' THEN 'Absen' ELSE p.status END as status,
                        COALESCE(p.jumlah_jam, 0),
                        p.keterangan,
                        p.created_at,
                        p.updated_at
                 FROM presensis p
                 LEFT JOIN pegawais pg ON CAST(p.pegawai_id AS TEXT) = CAST(pg.id AS TEXT) OR p.pegawai_id = pg.nomor_induk_pegawai"
            );
        } else {
            // MySQL/Postgres
            DB::statement(
                "INSERT INTO presensis_new (id, pegawai_id, tgl_presensi, jam_masuk, jam_keluar, status, jumlah_jam, keterangan, created_at, updated_at)
                 SELECT p.id,
                        COALESCE(pg.nomor_induk_pegawai, p.pegawai_id) as pegawai_id,
                        p.tgl_presensi,
                        p.jam_masuk,
                        p.jam_keluar,
                        IF(p.status = 'Alpa','Absen',p.status) as status,
                        COALESCE(p.jumlah_jam, 0),
                        p.keterangan,
                        p.created_at,
                        p.updated_at
                 FROM presensis p
                 LEFT JOIN pegawais pg ON p.pegawai_id = pg.id OR p.pegawai_id = pg.nomor_induk_pegawai"
            );
        }

        // 3) Ganti tabel lama dengan yang baru (tanpa FK untuk kompatibilitas lintas DB)
        Schema::drop('presensis');
        Schema::rename('presensis_new', 'presensis');
    }

    public function down(): void
    {
        // Tidak melakukan rollback penuh karena kompleks; hanya berhenti jika ada.
        if (Schema::hasTable('presensis_new')) {
            Schema::drop('presensis_new');
        }
    }
};
