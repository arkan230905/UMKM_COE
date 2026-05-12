<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop foreign key di tabel bops yang mengarah ke coas.kode_akun
        Schema::table('bops', function (Blueprint $table) {
            // Nama constraint di error: bops_kode_akun_foreign
            $table->dropForeign('bops_kode_akun_foreign');
        });

        // Drop foreign key di tabel coa_period_balances yang mengarah ke coas.kode_akun
        Schema::table('coa_period_balances', function (Blueprint $table) {
            // Nama constraint di error: coa_period_balances_kode_akun_foreign
            $table->dropForeign('coa_period_balances_kode_akun_foreign');
        });

        Schema::table('coas', function (Blueprint $table) {
            // Change existing columns if needed
            $table->string('kode_akun', 10)->change();
            
            // Add new columns
            $table->string('kategori_akun', 50)->after('tipe_akun');
            $table->boolean('is_akun_header')->default(false)->after('kategori_akun');
            $table->string('kode_induk', 10)->nullable()->after('is_akun_header');
            $table->enum('saldo_normal', ['debit', 'kredit'])->after('kode_induk');
            $table->text('keterangan')->nullable()->after('saldo_normal');
            
            // Add foreign key constraint for kode_induk
            $table->foreign('kode_induk')
                  ->references('kode_akun')
                  ->on('coas')
                  ->onDelete('cascade');
        });

        // Tambahkan kembali foreign key di tabel bops ke coas.kode_akun
        Schema::table('bops', function (Blueprint $table) {
            $table->foreign('kode_akun')
                  ->references('kode_akun')
                  ->on('coas')
                  ->onDelete('cascade');
        });

        // Tambahkan kembali foreign key di tabel coa_period_balances ke coas.kode_akun
        Schema::table('coa_period_balances', function (Blueprint $table) {
            $table->foreign('kode_akun')
                  ->references('kode_akun')
                  ->on('coas')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
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
            $table->dropColumn([
                'kategori_akun',
                'is_akun_header',
                'kode_induk',
                'saldo_normal',
                'keterangan'
            ]);

            // Revert kode_akun to string without length
            $table->string('kode_akun')->change();
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
};
