<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add kode_aset column if it doesn't exist
        if (!Schema::hasColumn('asets', 'kode_aset')) {
            Schema::table('asets', function (Blueprint $table) {
                $table->string('kode_aset')->nullable()->after('id');
            });
            
            // Generate kode_aset for existing records
            $asets = DB::table('asets')->whereNull('kode_aset')->get();
            foreach ($asets as $index => $aset) {
                $kode = 'AST-' . date('Ym') . '-' . str_pad($index + 1, 4, '0', STR_PAD_LEFT);
                DB::table('asets')->where('id', $aset->id)->update(['kode_aset' => $kode]);
            }
            
            // Make it unique and not nullable after populating
            Schema::table('asets', function (Blueprint $table) {
                $table->string('kode_aset')->unique()->nullable(false)->change();
            });
        }
        
        // Add other missing columns if they don't exist
        Schema::table('asets', function (Blueprint $table) {
            if (!Schema::hasColumn('asets', 'metode_penyusutan')) {
                $table->enum('metode_penyusutan', ['garis_lurus', 'saldo_menurun', 'sum_of_years_digits'])->default('garis_lurus')->after('status');
            }
            if (!Schema::hasColumn('asets', 'nilai_sisa')) {
                $table->decimal('nilai_sisa', 15, 2)->default(0)->after('nilai_buku');
            }
            if (!Schema::hasColumn('asets', 'akumulasi_penyusutan')) {
                $table->decimal('akumulasi_penyusutan', 15, 2)->default(0)->after('nilai_sisa');
            }
            if (!Schema::hasColumn('asets', 'tanggal_akuisisi')) {
                $table->date('tanggal_akuisisi')->nullable()->after('tanggal_beli');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asets', function (Blueprint $table) {
            if (Schema::hasColumn('asets', 'kode_aset')) {
                $table->dropColumn('kode_aset');
            }
            if (Schema::hasColumn('asets', 'metode_penyusutan')) {
                $table->dropColumn('metode_penyusutan');
            }
            if (Schema::hasColumn('asets', 'nilai_sisa')) {
                $table->dropColumn('nilai_sisa');
            }
            if (Schema::hasColumn('asets', 'akumulasi_penyusutan')) {
                $table->dropColumn('akumulasi_penyusutan');
            }
            if (Schema::hasColumn('asets', 'tanggal_akuisisi')) {
                $table->dropColumn('tanggal_akuisisi');
            }
        });
    }
};
