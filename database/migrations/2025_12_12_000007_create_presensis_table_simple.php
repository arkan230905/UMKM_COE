<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Buat tabel presensi jika belum ada
        if (!Schema::hasTable('presensis')) {
            Schema::create('presensis', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pegawai_id');
                $table->date('tgl_presensi');
                $table->string('jam_masuk', 5)->nullable();
                $table->string('jam_keluar', 5)->nullable();
                $table->string('status')->nullable();
                $table->integer('jumlah_menit_kerja')->default(0);
                $table->decimal('jumlah_jam_kerja', 5, 1)->default(0);
                $table->decimal('jumlah_jam', 5, 2)->default(0);
                $table->text('keterangan')->nullable();
                $table->timestamps();
                
                $table->unique(['pegawai_id', 'tgl_presensi']);
                $table->index('tgl_presensi');
                $table->index('status');
            });
        } else {
            // Jika tabel sudah ada, tambahkan kolom yang belum ada
            Schema::table('presensis', function (Blueprint $table) {
                if (!Schema::hasColumn('presensis', 'jumlah_menit_kerja')) {
                    $table->integer('jumlah_menit_kerja')->default(0)->after('status');
                }
                if (!Schema::hasColumn('presensis', 'jumlah_jam_kerja')) {
                    $table->decimal('jumlah_jam_kerja', 5, 1)->default(0)->after('jumlah_menit_kerja');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
