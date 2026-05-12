<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Add new columns if they don't exist
        if (!Schema::hasColumn('coas', 'kategori_akun')) {
            Schema::table('coas', function (Blueprint $table) {
                $table->string('kategori_akun')->after('tipe_akun')->nullable();
                $table->boolean('is_akun_header')->default(false)->after('kategori_akun');
                $table->string('kode_induk', 255)->nullable()->after('is_akun_header');
                $table->enum('saldo_normal', ['debit', 'kredit'])->default('debit')->after('kode_induk');
                $table->decimal('saldo_awal', 15, 2)->default(0)->after('saldo_normal');
                $table->date('tanggal_saldo_awal')->nullable()->after('saldo_awal');
                $table->text('keterangan')->nullable()->after('tanggal_saldo_awal');
                $table->boolean('posted_saldo_awal')->default(false)->after('keterangan');
            });
        }
    }

    public function down()
    {
        // This is a one-way migration
    }
};
