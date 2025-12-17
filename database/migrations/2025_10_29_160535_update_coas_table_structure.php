<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateCoasTableStructure extends Migration
{
    public function up(): void
    {
        // Drop foreign key di tabel bops yang mengarah ke coas.kode_akun
        try {
            Schema::table('bops', function (Blueprint $table) {
                $table->dropForeign(['kode_akun']);
            });
        } catch (\Exception $e) {
            // Foreign key tidak ada, skip
        }

        // Drop foreign key di tabel coa_period_balances yang mengarah ke coas.kode_akun
        try {
            Schema::table('coa_period_balances', function (Blueprint $table) {
                $table->dropForeign(['kode_akun']);
            });
        } catch (\Exception $e) {
            // Foreign key tidak ada, skip
        }

        // Ubah struktur tabel coas
        Schema::table('coas', function (Blueprint $table) {
            // Ubah kolom kode_akun jika belum diubah
            if (Schema::hasColumn('coas', 'kode_akun')) {
                $columnType = Schema::getColumnType('coas', 'kode_akun');
                if ($columnType !== 'string') {
                    $table->string('kode_akun', 10)->change();
                }
            }

            // Tambahkan kolom baru hanya jika belum ada
            if (!Schema::hasColumn('coas', 'kategori_akun')) {
                $table->string('kategori_akun', 50)->after('tipe_akun');
            }
            
            if (!Schema::hasColumn('coas', 'is_akun_header')) {
                $table->boolean('is_akun_header')->default(false)->after('kategori_akun');
            }
            
            if (!Schema::hasColumn('coas', 'kode_induk')) {
                $table->string('kode_induk', 10)->nullable()->after('is_akun_header');
            }
            
            if (!Schema::hasColumn('coas', 'saldo_normal')) {
                $table->enum('saldo_normal', ['debit', 'kredit'])->after('kode_induk');
            }
            
            if (!Schema::hasColumn('coas', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('saldo_normal');
            }
        });

        // Tambahkan foreign key constraint untuk kode_induk
        try {
            Schema::table('coas', function (Blueprint $table) {
                if (Schema::hasColumn('coas', 'kode_induk')) {
                    $table->foreign('kode_induk')
                          ->references('kode_akun')
                          ->on('coas')
                          ->onDelete('cascade');
                }
            });
        } catch (\Exception $e) {
            // Foreign key sudah ada atau error lainnya, skip
        }

        // Tambahkan kembali foreign key di tabel bops ke coas.kode_akun
        try {
            Schema::table('bops', function (Blueprint $table) {
                $table->foreign('kode_akun')
                      ->references('kode_akun')
                      ->on('coas')
                      ->onDelete('cascade');
            });
        } catch (\Exception $e) {
            // Foreign key sudah ada atau error lainnya, skip
        }
    }

    public function down(): void
    {
        // Drop foreign key bops -> coas sebelum mengubah struktur coas
        Schema::table('bops', function (Blueprint $table) {
            $table->dropForeign(['kode_akun']);
        });

        // Drop foreign key coa_period_balances -> coas sebelum mengubah struktur coas
        Schema::table('coa_period_balances', function (Blueprint $table) {
            $table->dropForeign(['kode_akun']);
        });

        Schema::table('coas', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['kode_induk']);

            // Drop columns
            $columnsToDrop = [];
            if (Schema::hasColumn('coas', 'kategori_akun')) $columnsToDrop[] = 'kategori_akun';
            if (Schema::hasColumn('coas', 'is_akun_header')) $columnsToDrop[] = 'is_akun_header';
            if (Schema::hasColumn('coas', 'kode_induk')) $columnsToDrop[] = 'kode_induk';
            if (Schema::hasColumn('coas', 'saldo_normal')) $columnsToDrop[] = 'saldo_normal';
            if (Schema::hasColumn('coas', 'keterangan')) $columnsToDrop[] = 'keterangan';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });

        // Tambahkan kembali foreign key bops -> coas sesuai struktur awal
        Schema::table('bops', function (Blueprint $table) {
            $table->foreign('kode_akun')
                  ->references('kode_akun')
                  ->on('coas')
                  ->onDelete('cascade');
        });

        // Tambahkan kembali foreign key coa_period_balances -> coas sesuai struktur awal
        Schema::table('coa_period_balances', function (Blueprint $table) {
            $table->foreign('kode_akun')
                  ->references('kode_akun')
                  ->on('coas')
                  ->onDelete('cascade');
        });
    }
}