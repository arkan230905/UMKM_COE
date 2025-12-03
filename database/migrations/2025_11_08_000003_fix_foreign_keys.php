<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Drop the bops table if it exists
        Schema::dropIfExists('bops');

        // Run the problematic migration that modifies coas table
        if (Schema::hasTable('coas') && !Schema::hasColumn('coas', 'kategori_akun')) {
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
        }

        // Recreate the bops table with correct structure
        if (!Schema::hasTable('bops')) {
            Schema::create('bops', function (Blueprint $table) {
                $table->id();
                $table->string('kode_akun', 10);
                $table->string('nama_biaya');
                $table->decimal('jumlah', 15, 2);
                $table->string('periode');
                $table->timestamps();

                $table->foreign('kode_akun')
                    ->references('kode_akun')
                    ->on('coas')
                    ->onDelete('cascade');
            });
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        // This is a one-way migration
    }
};
