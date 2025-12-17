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
        if (!Schema::hasTable('coas')) {
            return;
        }

        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::table('coas', function (Blueprint $table) {
            if (!Schema::hasColumn('coas', 'kategori_akun')) {
                $table->string('kategori_akun')->nullable();
            }
            if (!Schema::hasColumn('coas', 'kode_induk')) {
                $table->string('kode_induk')->nullable();
            }
            if (!Schema::hasColumn('coas', 'saldo_normal')) {
                $table->enum('saldo_normal', ['debit', 'kredit'])->nullable();
            }
            if (!Schema::hasColumn('coas', 'keterangan')) {
                $table->text('keterangan')->nullable();
            }
            if (!Schema::hasColumn('coas', 'is_akun_header')) {
                $table->boolean('is_akun_header')->default(false);
            }
            if (!Schema::hasColumn('coas', 'saldo_awal')) {
                $table->decimal('saldo_awal', 20, 2)->default(0);
            }
            if (!Schema::hasColumn('coas', 'tanggal_saldo_awal')) {
                $table->date('tanggal_saldo_awal')->nullable();
            }
            if (!Schema::hasColumn('coas', 'posted_saldo_awal')) {
                $table->boolean('posted_saldo_awal')->default(false);
            }
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
