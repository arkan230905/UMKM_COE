<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Nonaktifkan pengecekan foreign key sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Tambahkan kolom-kolom yang diperlukan
            $this->addColumnIfNotExists('coas', 'kategori_akun', 'varchar(255) NULL AFTER `tipe_akun`');
            $this->addColumnIfNotExists('coas', 'kode_induk', 'varchar(10) NULL AFTER `kategori_akun`');
            $this->addColumnIfNotExists('coas', 'saldo_normal', "enum('debit','kredit') NOT NULL DEFAULT 'debit' AFTER `kode_induk`");
            $this->addColumnIfNotExists('coas', 'keterangan', 'text NULL AFTER `saldo_normal`');
            $this->addColumnIfNotExists('coas', 'is_akun_header', 'tinyint(1) NOT NULL DEFAULT 0 AFTER `keterangan`');
            $this->addColumnIfNotExists('coas', 'saldo_awal', 'decimal(20,2) NOT NULL DEFAULT 0.00 AFTER `is_akun_header`');
            $this->addColumnIfNotExists('coas', 'tanggal_saldo_awal', 'date NULL AFTER `saldo_awal`');
            $this->addColumnIfNotExists('coas', 'posted_saldo_awal', 'tinyint(1) NOT NULL DEFAULT 0 AFTER `tanggal_saldo_awal`');

            // Tambahkan foreign key untuk kode_induk
            if (!Schema::hasColumn('coas', 'kode_induk')) {
                Schema::table('coas', function (Blueprint $table) {
                    $table->foreign('kode_induk')
                          ->references('kode_akun')
                          ->on('coas')
                          ->onDelete('set null')
                          ->onUpdate('cascade');
                });
            }

            // Update data yang sudah ada jika diperlukan
            DB::table('coas')->update([
                'kategori_akun' => DB::raw('tipe_akun'),
                'saldo_normal' => 'debit',
                'is_akun_header' => 0,
                'saldo_awal' => 0,
                'posted_saldo_awal' => 0
            ]);

        } catch (\Exception $e) {
            // Log error dan tampilkan pesan error yang ramah
            \Log::error('Gagal menjalankan migrasi: ' . $e->getMessage());
            throw $e;
        } finally {
            // Pastikan untuk mengaktifkan kembali pengecekan foreign key
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nonaktifkan pengecekan foreign key sementara
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        try {
            // Hapus foreign key terlebih dahulu
            if (Schema::hasTable('coas') && Schema::hasColumn('coas', 'kode_induk')) {
                Schema::table('coas', function (Blueprint $table) {
                    $table->dropForeign(['kode_induk']);
                });
            }

            // Hapus kolom yang telah ditambahkan
            $this->dropColumnIfExists('coas', 'kategori_akun');
            $this->dropColumnIfExists('coas', 'kode_induk');
            $this->dropColumnIfExists('coas', 'saldo_normal');
            $this->dropColumnIfExists('coas', 'keterangan');
            $this->dropColumnIfExists('coas', 'is_akun_header');
            $this->dropColumnIfExists('coas', 'saldo_awal');
            $this->dropColumnIfExists('coas', 'tanggal_saldo_awal');
            $this->dropColumnIfExists('coas', 'posted_saldo_awal');

        } catch (\Exception $e) {
            // Log error
            \Log::error('Gagal melakukan rollback migrasi: ' . $e->getMessage());
            throw $e;
        } finally {
            // Aktifkan kembali pengecekan foreign key
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    /**
     * Tambahkan kolom jika belum ada
     */
    private function addColumnIfNotExists(string $table, string $column, string $definition): void
    {
        if (!Schema::hasColumn($table, $column)) {
            DB::statement("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
        }
    }

    /**
     * Hapus kolom jika ada
     */
    private function dropColumnIfExists(string $table, string $column): void
    {
        if (Schema::hasColumn($table, $column)) {
            DB::statement("ALTER TABLE `{$table}` DROP COLUMN `{$column}`");
        }
    }
};
