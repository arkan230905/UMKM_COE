<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Cek apakah tabel sudah ada
        if (!Schema::hasTable('jabatans')) {
            Schema::create('jabatans', function (Blueprint $table) {
                $table->id();
                $table->string('kode_jabatan', 10)->unique();
                $table->string('nama', 100);
                $table->string('kategori', 50)->nullable();
                $table->decimal('gaji_pokok', 15, 2)->default(0);
                $table->decimal('tunjangan', 15, 2)->default(0);
                $table->decimal('asuransi', 15, 2)->default(0);
                $table->decimal('tarif_lembur', 15, 2)->default(0);
                $table->text('deskripsi')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            // Jika tabel sudah ada, tambahkan kolom yang mungkin belum ada
            Schema::table('jabatans', function (Blueprint $table) {
                if (!Schema::hasColumn('jabatans', 'kode_jabatan')) {
                    $table->string('kode_jabatan', 10)->unique()->after('id');
                }
                if (!Schema::hasColumn('jabatans', 'kategori')) {
                    $table->string('kategori', 50)->nullable()->after('nama');
                }
                if (!Schema::hasColumn('jabatans', 'gaji_pokok')) {
                    $table->decimal('gaji_pokok', 15, 2)->default(0)->after('kategori');
                }
                if (!Schema::hasColumn('jabatans', 'tunjangan')) {
                    $table->decimal('tunjangan', 15, 2)->default(0)->after('gaji_pokok');
                }
                if (!Schema::hasColumn('jabatans', 'asuransi')) {
                    $table->decimal('asuransi', 15, 2)->default(0)->after('tunjangan');
                }
                if (!Schema::hasColumn('jabatans', 'tarif_lembur')) {
                    $table->decimal('tarif_lembur', 15, 2)->default(0)->after('asuransi');
                }
                if (!Schema::hasColumn('jabatans', 'deskripsi')) {
                    $table->text('deskripsi')->nullable()->after('tarif_lembur');
                }
            });
        }
    }

    public function down()
    {
        // Jangan hapus tabel jika sudah ada data
        if (Schema::hasTable('jabatans')) {
            if (Schema::hasColumn('jabatans', 'deleted_at')) {
                // Jika menggunakan soft deletes, hapus permanen dulu
                \DB::table('jabatans')->whereNotNull('deleted_at')->delete();
            }
            
            // Hapus foreign key constraints terlebih dahulu
            Schema::table('jabatans', function (Blueprint $table) {
                // Hapus foreign key constraints jika ada
                $foreignKeys = [];
                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $tableDetails = $sm->listTableDetails('jabatans');
                
                foreach ($tableDetails->getForeignKeys() as $fk) {
                    $foreignKeys[] = $fk->getName();
                }
                
                foreach ($foreignKeys as $fk) {
                    $table->dropForeign($fk);
                }
            });
            
            // Hapus tabel
            Schema::dropIfExists('jabatans');
        }
    }
};
