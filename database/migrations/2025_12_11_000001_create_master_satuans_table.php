<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Cek apakah tabel sudah ada
        if (Schema::hasTable('satuans')) {
            // Tambahkan kolom yang belum ada
            Schema::table('satuans', function (Blueprint $table) {
                if (!Schema::hasColumn('satuans', 'deskripsi')) {
                    $table->string('deskripsi')->nullable()->after('nama');
                }
                if (!Schema::hasColumn('satuans', 'kategori')) {
                    $table->enum('kategori', ['berat', 'volume', 'panjang', 'jumlah', 'waktu'])->default('jumlah')->after('deskripsi');
                }
                if (!Schema::hasColumn('satuans', 'faktor_ke_dasar')) {
                    $table->decimal('faktor_ke_dasar', 15, 6)->default(1)->after('kategori');
                }
                if (!Schema::hasColumn('satuans', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('faktor_ke_dasar');
                }
            });
        } else {
            // Buat tabel baru
            Schema::create('satuans', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 10)->unique();
                $table->string('nama', 50);
                $table->string('deskripsi')->nullable();
                $table->enum('kategori', ['berat', 'volume', 'panjang', 'jumlah', 'waktu'])->default('jumlah');
                $table->decimal('faktor_ke_dasar', 15, 6)->default(1);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::table('satuans', function (Blueprint $table) {
            $table->dropColumn(['deskripsi', 'kategori', 'faktor_ke_dasar', 'is_active']);
        });
    }
};
