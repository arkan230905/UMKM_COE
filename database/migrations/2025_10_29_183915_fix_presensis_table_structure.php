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
        // Buat tabel sementara dengan struktur baru
        Schema::create('presensis_new', function (Blueprint $table) {
            $table->id();
            $table->string('pegawai_id');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->integer('jumlah_jam')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            // Tambahkan foreign key constraint setelah tabel pegawais selesai diupdate
        });

        // Salin data dari tabel lama ke tabel baru
        $presensis = \DB::table('presensis')->get();
        
        foreach ($presensis as $presensi) {
            // Gunakan tgl_presensi jika ada, atau tanggal jika ada
            $tanggal = $presensi->tgl_presensi ?? $presensi->tanggal ?? null;
            
            if ($tanggal) {
                \DB::table('presensis_new')->insert([
                    'id' => $presensi->id,
                    'pegawai_id' => $presensi->pegawai_id,
                    'tanggal' => $tanggal,
                    'jam_masuk' => $presensi->jam_masuk ?? null,
                    'jam_pulang' => $presensi->jam_keluar ?? $presensi->jam_pulang ?? null,
                    'jumlah_jam' => $presensi->jumlah_jam ?? 0,
                    'keterangan' => $presensi->keterangan ?? null,
                    'created_at' => $presensi->created_at,
                    'updated_at' => $presensi->updated_at,
                ]);
            }
        }

        // Hapus tabel lama
        Schema::dropIfExists('presensis');
        
        // Ganti nama tabel baru
        Schema::rename('presensis_new', 'presensis');
        
        // Tambahkan foreign key constraint setelah tabel pegawais selesai diupdate
        Schema::table('presensis', function (Blueprint $table) {
            $table->foreign('pegawai_id')
                  ->references('nomor_induk_pegawai')
                  ->on('pegawais')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Kembalikan ke struktur lama jika diperlukan
        Schema::create('presensis_old', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pegawai_id');
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->integer('jumlah_jam')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            $table->foreign('pegawai_id')
                  ->references('id')
                  ->on('pegawais')
                  ->onDelete('cascade');
        });

        // Salin data kembali ke struktur lama
        $presensis = \DB::table('presensis')->get();
        
        foreach ($presensis as $presensi) {
            \DB::table('presensis_old')->insert([
                'id' => $presensi->id,
                'pegawai_id' => $presensi->pegawai_id, // Ini akan error jika pegawai_id tidak bisa di-convert ke integer
                'tanggal' => $presensi->tanggal,
                'jam_masuk' => $presensi->jam_masuk,
                'jam_pulang' => $presensi->jam_pulang,
                'jumlah_jam' => $presensi->jumlah_jam ?? 0,
                'keterangan' => $presensi->keterangan,
                'created_at' => $presensi->created_at,
                'updated_at' => $presensi->updated_at,
            ]);
        }

        // Hapus tabel baru dan ganti dengan yang lama
        Schema::dropIfExists('presensis');
        Schema::rename('presensis_old', 'presensis');
    }
};
