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
        // Add missing columns to penggajians table if they don't exist
        Schema::table('penggajians', function (Blueprint $table) {
            // Check and add tarif_per_jam
            if (!Schema::hasColumn('penggajians', 'tarif_per_jam')) {
                $table->decimal('tarif_per_jam', 12, 2)->default(0)->after('gaji_pokok');
            }
            
            // Check and add status_posting
            if (!Schema::hasColumn('penggajians', 'status_posting')) {
                $table->string('status_posting')->default('belum_posting')->after('tanggal_dibayar');
            }
            
            // Check and add tanggal_posting
            if (!Schema::hasColumn('penggajians', 'tanggal_posting')) {
                $table->date('tanggal_posting')->nullable()->after('status_posting');
            }
            
            // Check and add mode_input
            if (!Schema::hasColumn('penggajians', 'mode_input')) {
                $table->enum('mode_input', ['harian', 'bulanan'])->default('bulanan')->after('updated_at');
            }
            
            // Check and add total_produk_bulanan
            if (!Schema::hasColumn('penggajians', 'total_produk_bulanan')) {
                $table->integer('total_produk_bulanan')->nullable()->after('total_produk_bulan');
            }
            
            // Check and add pembulatan_aktif
            if (!Schema::hasColumn('penggajians', 'pembulatan_aktif')) {
                $table->boolean('pembulatan_aktif')->default(false)->after('total_produk_bulanan');
            }
            
            // Check and add pembulatan_step
            if (!Schema::hasColumn('penggajians', 'pembulatan_step')) {
                $table->integer('pembulatan_step')->nullable()->after('pembulatan_aktif');
            }
            
            // Check and add nominal_pembulatan
            if (!Schema::hasColumn('penggajians', 'nominal_pembulatan')) {
                $table->integer('nominal_pembulatan')->default(0)->after('pembulatan_step');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            // Drop columns if they exist
            if (Schema::hasColumn('penggajians', 'tarif_per_jam')) {
                $table->dropColumn('tarif_per_jam');
            }
            if (Schema::hasColumn('penggajians', 'status_posting')) {
                $table->dropColumn('status_posting');
            }
            if (Schema::hasColumn('penggajians', 'tanggal_posting')) {
                $table->dropColumn('tanggal_posting');
            }
            if (Schema::hasColumn('penggajians', 'mode_input')) {
                $table->dropColumn('mode_input');
            }
            if (Schema::hasColumn('penggajians', 'total_produk_bulanan')) {
                $table->dropColumn('total_produk_bulanan');
            }
            if (Schema::hasColumn('penggajians', 'pembulatan_aktif')) {
                $table->dropColumn('pembulatan_aktif');
            }
            if (Schema::hasColumn('penggajians', 'pembulatan_step')) {
                $table->dropColumn('pembulatan_step');
            }
            if (Schema::hasColumn('penggajians', 'nominal_pembulatan')) {
                $table->dropColumn('nominal_pembulatan');
            }
        });
    }
};
