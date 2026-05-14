<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Membuat tabel kategori_produks dan menambahkan kolom kategori_id ke tabel produks.
     */
    public function up(): void
    {
        // 1. Buat tabel kategori_produks jika belum ada
        if (!Schema::hasTable('kategori_produks')) {
            Schema::create('kategori_produks', function (Blueprint $table) {
                $table->id();
                $table->string('kode_kategori', 20)->unique();
                $table->string('nama', 100);
                $table->text('deskripsi')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Tambahkan kolom kategori_id ke tabel produks jika belum ada
        if (Schema::hasTable('produks') && !Schema::hasColumn('produks', 'kategori_id')) {
            Schema::table('produks', function (Blueprint $table) {
                $table->unsignedBigInteger('kategori_id')->nullable()->after('nama_produk');
                $table->foreign('kategori_id')
                      ->references('id')
                      ->on('kategori_produks')
                      ->nullOnDelete();
            });
        }

        // 3. Isi data default kategori jika tabel kosong
        if (\DB::table('kategori_produks')->count() === 0) {
            \DB::table('kategori_produks')->insert([
                [
                    'kode_kategori' => 'MKN',
                    'nama'          => 'Makanan',
                    'deskripsi'     => 'Produk makanan',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'kode_kategori' => 'MNM',
                    'nama'          => 'Minuman',
                    'deskripsi'     => 'Produk minuman',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'kode_kategori' => 'PKT',
                    'nama'          => 'Paket',
                    'deskripsi'     => 'Produk paket bundling',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
                [
                    'kode_kategori' => 'LNY',
                    'nama'          => 'Lainnya',
                    'deskripsi'     => 'Produk lainnya',
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus foreign key dan kolom kategori_id dari produks
        if (Schema::hasTable('produks') && Schema::hasColumn('produks', 'kategori_id')) {
            Schema::table('produks', function (Blueprint $table) {
                $table->dropForeign(['kategori_id']);
                $table->dropColumn('kategori_id');
            });
        }

        // Hapus tabel kategori_produks
        Schema::dropIfExists('kategori_produks');
    }
};
