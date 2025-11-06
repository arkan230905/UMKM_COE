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
        $hasNip = Schema::hasColumn('pegawais', 'nomor_induk_pegawai');

        // Detect presensis column names
        $colDate = Schema::hasColumn('presensis', 'tgl_presensi') ? 'tgl_presensi' : (Schema::hasColumn('presensis','tanggal') ? 'tanggal' : null);
        $colIn   = Schema::hasColumn('presensis', 'jam_masuk') ? 'jam_masuk' : null;
        $colOut  = Schema::hasColumn('presensis', 'jam_keluar') ? 'jam_keluar' : null;
        $colSt   = Schema::hasColumn('presensis', 'status') ? 'status' : null;
        $colJJ   = Schema::hasColumn('presensis', 'jumlah_jam') ? 'jumlah_jam' : null;
        $colKet  = Schema::hasColumn('presensis', 'keterangan') ? 'keterangan' : null;

        // Build selectable expressions
        $exprDate = $colDate ? "p.$colDate" : "p.created_at";
        $exprIn   = $colIn ? "p.$colIn" : "NULL";
        $exprOut  = $colOut ? "p.$colOut" : "NULL";
        $exprSt   = $colSt ? ($driver==='sqlite' ? "CASE WHEN p.$colSt = 'Alpa' THEN 'Absen' ELSE p.$colSt END" : "IF(p.$colSt = 'Alpa','Absen',p.$colSt)") : "'Hadir'";
        $exprJJ   = $colJJ ? "COALESCE(p.$colJJ, 0)" : "0";
        $exprKet  = $colKet ? "p.$colKet" : "NULL";

        if ($driver === 'sqlite') {
            $sql = "INSERT INTO presensis_new (id, pegawai_id, tgl_presensi, jam_masuk, jam_keluar, status, jumlah_jam, keterangan, created_at, updated_at)
                    SELECT p.id,
                           ".($hasNip ? "COALESCE(pg.nomor_induk_pegawai, p.pegawai_id)" : "p.pegawai_id")." as pegawai_id,
                           $exprDate,
                           $exprIn,
                           $exprOut,
                           $exprSt as status,
                           $exprJJ,
                           $exprKet,
                           p.created_at,
                           p.updated_at
                    FROM presensis p ".($hasNip ? "LEFT JOIN pegawais pg ON CAST(p.pegawai_id AS TEXT) = CAST(pg.id AS TEXT) OR p.pegawai_id = pg.nomor_induk_pegawai" : "");
            DB::statement($sql);
        } else {
            $sql = "INSERT INTO presensis_new (id, pegawai_id, tgl_presensi, jam_masuk, jam_keluar, status, jumlah_jam, keterangan, created_at, updated_at)
                    SELECT p.id,
                           ".($hasNip ? "COALESCE(pg.nomor_induk_pegawai, p.pegawai_id)" : "p.pegawai_id")." as pegawai_id,
                           $exprDate,
                           $exprIn,
                           $exprOut,
                           $exprSt as status,
                           $exprJJ,
                           $exprKet,
                           p.created_at,
                           p.updated_at
                    FROM presensis p ".($hasNip ? "LEFT JOIN pegawais pg ON p.pegawai_id = pg.id OR p.pegawai_id = pg.nomor_induk_pegawai" : "");
            DB::statement($sql);
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
