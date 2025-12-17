<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // First, check if the columns already exist
        if (!Schema::hasColumn('coas', 'kategori_akun')) {
            Schema::table('coas', function (Blueprint $table) {
                $table->string('kategori_akun')->after('tipe_akun')->nullable();
                $table->boolean('is_akun_header')->default(false)->after('kategori_akun');
                $table->string('kode_induk', 10)->nullable()->after('is_akun_header');
                $table->enum('saldo_normal', ['debit', 'kredit'])->default('debit')->after('kode_induk');
                $table->decimal('saldo_awal', 15, 2)->default(0)->after('saldo_normal');
                $table->date('tanggal_saldo_awal')->nullable()->after('saldo_awal');
                $table->text('keterangan')->nullable()->after('tanggal_saldo_awal');
                $table->boolean('posted_saldo_awal')->default(false)->after('keterangan');
            });

            // Add foreign key constraint after all columns are added
            Schema::table('coas', function (Blueprint $table) {
                $table->foreign('kode_induk')
                      ->references('kode_akun')
                      ->on('coas')
                      ->onDelete('set null');
            });
        }
    }

    public function down()
    {
        Schema::table('coas', function (Blueprint $table) {
            if (Schema::hasColumn('coas', 'kode_induk')) {
                $table->dropForeign(['kode_induk']);
            }
            $columns = [
                'kategori_akun',
                'is_akun_header',
                'kode_induk',
                'saldo_normal',
                'saldo_awal',
                'tanggal_saldo_awal',
                'keterangan',
                'posted_saldo_awal'
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('coas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
