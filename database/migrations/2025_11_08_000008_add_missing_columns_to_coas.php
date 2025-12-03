<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('coas', function (Blueprint $table) {
            if (!Schema::hasColumn('coas', 'kategori_akun')) {
                $table->string('kategori_akun')->nullable()->after('tipe_akun');
            }
            if (!Schema::hasColumn('coas', 'is_akun_header')) {
                $table->boolean('is_akun_header')->default(false)->after('kategori_akun');
            }
            if (!Schema::hasColumn('coas', 'kode_induk')) {
                $table->string('kode_induk', 10)->nullable()->after('is_akun_header');
            }
            if (!Schema::hasColumn('coas', 'saldo_normal')) {
                $table->enum('saldo_normal', ['debit', 'kredit'])->default('debit')->after('kode_induk');
            }
            if (!Schema::hasColumn('coas', 'saldo_awal')) {
                $table->decimal('saldo_awal', 15, 2)->default(0)->after('saldo_normal');
            }
            if (!Schema::hasColumn('coas', 'tanggal_saldo_awal')) {
                $table->date('tanggal_saldo_awal')->nullable()->after('saldo_awal');
            }
            if (!Schema::hasColumn('coas', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('tanggal_saldo_awal');
            }
            if (!Schema::hasColumn('coas', 'posted_saldo_awal')) {
                $table->boolean('posted_saldo_awal')->default(false)->after('keterangan');
            }
        });
    }

    public function down()
    {
        // This is a one-way migration to prevent data loss
    }
};
