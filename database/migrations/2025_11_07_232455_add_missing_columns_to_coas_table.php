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
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('coas', function (Blueprint $table) {
            $table->string('kategori_akun')->after('tipe_akun')->nullable();
            $table->string('kode_induk')->after('kategori_akun')->nullable();
            $table->enum('saldo_normal', ['debit', 'kredit'])->after('kode_induk');
            $table->text('keterangan')->after('saldo_normal')->nullable();
            $table->boolean('is_akun_header')->default(false)->after('keterangan');
            $table->decimal('saldo_awal', 20, 2)->default(0)->after('is_akun_header');
            $table->date('tanggal_saldo_awal')->after('saldo_awal')->nullable();
            $table->boolean('posted_saldo_awal')->default(false)->after('tanggal_saldo_awal');
        });

        // Add foreign key for kode_induk
        Schema::table('coas', function (Blueprint $table) {
            $table->foreign('kode_induk')
                  ->references('kode_akun')
                  ->on('coas')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('coas', function (Blueprint $table) {
            // Drop foreign key first
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['kode_induk']);
            }
            
            $table->dropColumn([
                'kategori_akun',
                'kode_induk',
                'saldo_normal',
                'keterangan',
                'is_akun_header',
                'saldo_awal',
                'tanggal_saldo_awal',
                'posted_saldo_awal'
            ]);
        });

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
