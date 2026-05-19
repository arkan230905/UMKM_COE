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
        // Fix pelunasan_utangs.akun_kas_id
        if (Schema::hasTable('pelunasan_utangs') && Schema::hasColumn('pelunasan_utangs', 'akun_kas_id')) {
            try {
                Schema::table('pelunasan_utangs', function (Blueprint $table) {
                    $table->dropForeign(['akun_kas_id']);
                });
                Schema::table('pelunasan_utangs', function (Blueprint $table) {
                    $table->foreign('akun_kas_id')->references('id')->on('coas')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key might not exist or already correct
            }
        }
        
        // Fix produks.coa_persediaan_id (if exists and uses id reference)
        if (Schema::hasTable('produks') && Schema::hasColumn('produks', 'coa_persediaan_id')) {
            try {
                Schema::table('produks', function (Blueprint $table) {
                    $table->dropForeign(['coa_persediaan_id']);
                });
                Schema::table('produks', function (Blueprint $table) {
                    $table->foreign('coa_persediaan_id')->references('id')->on('coas')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key might not exist or already correct
            }
        }
        
        // Fix asets.coa_id
        if (Schema::hasTable('asets') && Schema::hasColumn('asets', 'coa_id')) {
            try {
                Schema::table('asets', function (Blueprint $table) {
                    $table->dropForeign(['coa_id']);
                });
                Schema::table('asets', function (Blueprint $table) {
                    $table->foreign('coa_id')->references('id')->on('coas')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key might not exist or already correct
            }
        }
        
        // Fix retur_kompensasis.akun_id
        if (Schema::hasTable('retur_kompensasis') && Schema::hasColumn('retur_kompensasis', 'akun_id')) {
            try {
                Schema::table('retur_kompensasis', function (Blueprint $table) {
                    $table->dropForeign(['akun_id']);
                });
                Schema::table('retur_kompensasis', function (Blueprint $table) {
                    $table->foreign('akun_id')->references('id')->on('coas')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Foreign key might not exist or already correct
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert pelunasan_utangs.akun_kas_id
        if (Schema::hasTable('pelunasan_utangs') && Schema::hasColumn('pelunasan_utangs', 'akun_kas_id')) {
            try {
                Schema::table('pelunasan_utangs', function (Blueprint $table) {
                    $table->dropForeign(['akun_kas_id']);
                });
                Schema::table('pelunasan_utangs', function (Blueprint $table) {
                    $table->foreign('akun_kas_id')->references('id')->on('accounts')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Ignore
            }
        }
        
        // Revert produks.coa_persediaan_id
        if (Schema::hasTable('produks') && Schema::hasColumn('produks', 'coa_persediaan_id')) {
            try {
                Schema::table('produks', function (Blueprint $table) {
                    $table->dropForeign(['coa_persediaan_id']);
                });
                Schema::table('produks', function (Blueprint $table) {
                    $table->foreign('coa_persediaan_id')->references('id')->on('accounts')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Ignore
            }
        }
        
        // Revert asets.coa_id
        if (Schema::hasTable('asets') && Schema::hasColumn('asets', 'coa_id')) {
            try {
                Schema::table('asets', function (Blueprint $table) {
                    $table->dropForeign(['coa_id']);
                });
                Schema::table('asets', function (Blueprint $table) {
                    $table->foreign('coa_id')->references('id')->on('accounts')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Ignore
            }
        }
        
        // Revert retur_kompensasis.akun_id
        if (Schema::hasTable('retur_kompensasis') && Schema::hasColumn('retur_kompensasis', 'akun_id')) {
            try {
                Schema::table('retur_kompensasis', function (Blueprint $table) {
                    $table->dropForeign(['akun_id']);
                });
                Schema::table('retur_kompensasis', function (Blueprint $table) {
                    $table->foreign('akun_id')->references('id')->on('accounts')->onDelete('set null');
                });
            } catch (\Exception $e) {
                // Ignore
            }
        }
    }
};
