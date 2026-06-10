<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations - Ensure ALL penggajians columns exist with proper defaults
     */
    public function up(): void
    {
        Schema::table('penggajians', function (Blueprint $table) {
            // Core columns with defaults
            if (!Schema::hasColumn('penggajians', 'bulan')) {
                $table->string('bulan', 20)->default('01');
            }
            
            if (!Schema::hasColumn('penggajians', 'tahun')) {
                $table->string('tahun', 4)->default(date('Y'));
            }
            
            if (!Schema::hasColumn('penggajians', 'total_jam_kerja')) {
                $table->integer('total_jam_kerja')->default(0);
            }
            
            if (!Schema::hasColumn('penggajians', 'mode_input')) {
                $table->enum('mode_input', ['harian', 'bulanan'])->default('bulanan');
            }
            
            if (!Schema::hasColumn('penggajians', 'total_produk_bulanan')) {
                $table->integer('total_produk_bulanan')->nullable();
            }
            
            if (!Schema::hasColumn('penggajians', 'pembulatan_aktif')) {
                $table->boolean('pembulatan_aktif')->default(false);
            }
            
            if (!Schema::hasColumn('penggajians', 'pembulatan_step')) {
                $table->integer('pembulatan_step')->default(1000);
            }
            
            if (!Schema::hasColumn('penggajians', 'nominal_pembulatan')) {
                $table->decimal('nominal_pembulatan', 15, 2)->default(0);
            }
        });
        
        // Make bulan and tahun nullable if they exist without default
        Schema::table('penggajians', function (Blueprint $table) {
            if (Schema::hasColumn('penggajians', 'bulan')) {
                $table->string('bulan', 20)->nullable()->default('01')->change();
            }
            if (Schema::hasColumn('penggajians', 'tahun')) {
                $table->string('tahun', 4)->nullable()->default(date('Y'))->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Do nothing - we don't want to drop essential columns
    }
};
